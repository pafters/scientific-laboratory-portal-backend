<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\BitrixCache;
use Phosagro\Iblocks;
use Phosagro\Object\Task;
use Phosagro\Object\TaskFiles;
use Phosagro\Object\TaskPlace;
use Phosagro\Object\TaskType;
use Phosagro\Object\TaskVideo;
use Phosagro\System\UrlManager;
use Phosagro\Util\Text;

/**
 * @extends AbstractIblockElementManager<Task>
 */
final class TaskManager extends AbstractIblockElementManager
{
    /** @var null|array<int,string> */
    private ?array $taskTypeIds = null;

    public function __construct(
        private readonly FormManager $forms,
        private readonly UrlManager $urls,
    ) {}

    protected function createFromBitrixData(array $row): object
    {
        $activeFromDate = null;
        $activeFrom = trim((string) $row['ACTIVE_FROM']);

        if ('' !== $activeFrom) {
            $activeFromTime = MakeTimeStamp($activeFrom);
            if ($activeFromTime) {
                $activeFromDate = new \DateTimeImmutable(sprintf('@%d', $activeFromTime));
            }
        }

        $activeToDate = null;
        $activeTo = trim((string) $row['ACTIVE_TO']);
        if ('' !== $activeTo) {
            $activeToTime = MakeTimeStamp($activeTo);
            if ($activeToTime) {
                $activeToDate = new \DateTimeImmutable(sprintf('@%d', $activeToTime));
            }
        }

        $detailText = Text::bitrix(trim((string) $row['DETAIL_TEXT']), trim((string) $row['DETAIL_TEXT_TYPE']));
        $previewText = Text::bitrix(trim((string) $row['PREVIEW_TEXT']), trim((string) $row['PREVIEW_TEXT_TYPE']));

        if ('' !== $detailText) {
            $descriptionHtml = $detailText;
        } else {
            $descriptionHtml = $previewText;
        }

        /** @var string[] $fileTypeIndex */
        $fileTypeIndex = [];

        foreach ((array) $row['PROPERTY_FILE_TYPES_VALUE'] as $fileType) {
            if (\is_string($fileType)) {
                $trimmed = trim($fileType);
                if (str_starts_with($trimmed, '.')) {
                    $trimmed = Text::substring($trimmed, 1);
                }
                if ('' !== $trimmed) {
                    $fileTypeIndex["~{$trimmed}"] = $trimmed;
                }
            }
        }

        $videoFileUrl = '';

        $videoFileIdentifier = $row['PROPERTY_VIDEO_FILE_VALUE'];
        $videoFileIdentifier = (null === $videoFileIdentifier) ? null : (int) $videoFileIdentifier;

        if (null !== $videoFileIdentifier) {
            $videoFilePath = \CFile::GetPath($videoFileIdentifier);
            $videoFilePath = \is_string($videoFilePath) ? trim($videoFilePath) : '';
            if ('' !== $videoFilePath) {
                $videoFileUrl = $this->urls->makeAbsolute($videoFilePath);
            }
        }

        $correctAnswerLimit = $row['PROPERTY_CORRECT_ANSWER_LIMIT_VALUE'];
        $correctAnswerLimit = (null === $correctAnswerLimit) ? null : (int) $correctAnswerLimit;

        return new Task(
            $correctAnswerLimit,
            (int) $row['PROPERTY_EVENT_VALUE'],
            new TaskFiles(
                (int) $row['PROPERTY_MAX_FILES_VALUE'],
                array_values($fileTypeIndex),
            ),
            $this->forms->findForm((int) $row['PROPERTY_FORM_VALUE']),
            new TaskPlace(
                (float) $row['PROPERTY_LATITUDE_VALUE'],
                (float) $row['PROPERTY_LONGITUDE_VALUE'],
            ),
            'Y' === $row['ACTIVE'],
            (int) $row['PROPERTY_BONUS_VALUE'],
            $descriptionHtml,
            (int) $row['PROPERTY_MAX_DURATION_VALUE'],
            $activeToDate,
            (int) $row['ID'],
            trim((string) $row['NAME']),
            '' !== trim((string) $row['PROPERTY_REQUIRED_VALUE']),
            $activeFromDate,
            $this->getTaskTypeById((int) $row['PROPERTY_TASK_TYPE_VALUE']),
            new TaskVideo(
                $videoFileUrl,
                trim((string) $row['PROPERTY_VIDEO_VALUE']),
            ),
        );
    }

    protected function getBitrixFields(): array
    {
        return [
            'ACTIVE',
            'ACTIVE_FROM',
            'ACTIVE_TO',
            'DETAIL_TEXT',
            'DETAIL_TEXT_TYPE',
            'ID',
            'NAME',
            'PREVIEW_TEXT',
            'PREVIEW_TEXT_TYPE',
            'PROPERTY_BONUS',
            'PROPERTY_CORRECT_ANSWER_LIMIT',
            'PROPERTY_EVENT',
            'PROPERTY_FILE_TYPES',
            'PROPERTY_FORM',
            'PROPERTY_LATITUDE',
            'PROPERTY_LONGITUDE',
            'PROPERTY_MAX_DURATION',
            'PROPERTY_MAX_FILES',
            'PROPERTY_REQUIRED',
            'PROPERTY_TASK_TYPE',
            'PROPERTY_VIDEO',
            'PROPERTY_VIDEO_FILE',
        ];
    }

    private function getTaskTypeById(int $identifier): TaskType
    {
        return TaskType::tryFrom($this->getTaskTypeIds()[$identifier] ?? null) ?? TaskType::BASE_TASK;
    }

    /**
     * @return array<int,string>
     */
    private function getTaskTypeIds(): array
    {
        return $this->taskTypeIds ??= BitrixCache::get('/phosagro_task_type_ids', $this->loadTaskTypeIds(...));
    }

    /**
     * @return array<int,string>
     */
    private function loadTaskTypeIds(): array
    {
        /** @var array<int,string> $result */
        $result = [];

        $found = \CIBlockElement::GetList(
            [
                'id' => 'asc',
            ],
            [
                'IBLOCK_ID' => Iblocks::taskTypeId(),
            ],
            false,
            false,
            [
                'CODE',
                'ID',
            ],
        );

        while ($row = $found->Fetch()) {
            $result[(int) $row['ID']] = (string) $row['CODE'];
        }

        return $result;
    }
}
