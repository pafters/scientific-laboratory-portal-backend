<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\Object\Bitrix\User;
use Phosagro\Object\Question;
use Phosagro\Object\QuestionType;
use Phosagro\System\Iblock\Properties;
use Phosagro\Util\Date;
use Phosagro\Util\DateFormat;
use Phosagro\Util\Text;

/**
 * @extends AbstractIblockElementManager<Question>
 */
final class QuestionManager extends AbstractIblockElementManager
{
    /** @var array<int,QuestionType> */
    private ?array $questionTypeIndex = null;

    public function __construct(
        private readonly Properties $properties,
    ) {}

    public function getUserQuestion(User $user, int $questionIdentifier): Question
    {
        return $this->getSingleElement([
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
            'ID' => $questionIdentifier,
            'PROPERTY_USER' => $user->userIdentifier,
        ]);
    }

    /**
     * @return Question[]
     */
    public function getUserQuestions(User $user, int $page = 1): array
    {
        return $this->findAllElements([
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
            'PROPERTY_USER' => $user->userIdentifier,
        ], nav: [
            'bShowAll' => false,
            'checkOutOfRange' => true,
            'iNumPage' => (int) $page,
            'nPageSize' => 10,
        ]);
    }

    protected function createFromBitrixData(array $row): Question
    {
        return new Question(
            Date::fromFormat(trim((string) ($row['TIMESTAMP_X'] ?? '')), DateFormat::BITRIX),
            Date::fromFormat(trim((string) ($row['DATE_CREATE'] ?? '')), DateFormat::BITRIX),
            (null === $row['PROPERTY_EVENT_VALUE']) ? null : (int) $row['PROPERTY_EVENT_VALUE'],
            (null === $row['PROPERTY_FILE_VALUE']) ? null : (int) $row['PROPERTY_FILE_VALUE'],
            (int) $row['ID'],
            (int) $row['PROPERTY_MODERATOR_VALUE'],
            trim((string) $row['NAME']),
            Text::bitrix(trim((string) $row['PREVIEW_TEXT']), trim((string) $row['PREVIEW_TEXT_TYPE'])),
            Text::bitrix(trim((string) $row['DETAIL_TEXT']), trim((string) $row['DETAIL_TEXT_TYPE'])),
            (int) $row['SORT'],
            (int) $row['PROPERTY_TOPIC_VALUE'],
            $this->getQuestionType((int) $row['PROPERTY_TYPE_ENUM_ID']),
            trim((string) $row['PROPERTY_URL_VALUE']),
            (int) $row['PROPERTY_USER_VALUE'],
        );
    }

    protected function getBitrixFields(): array
    {
        return [
            'DATE_CREATE',
            'DETAIL_TEXT',
            'DETAIL_TEXT_TYPE',
            'ID',
            'NAME',
            'PREVIEW_TEXT',
            'PREVIEW_TEXT_TYPE',
            'PROPERTY_EVENT',
            'PROPERTY_FILE',
            'PROPERTY_MODERATOR',
            'PROPERTY_TOPIC',
            'PROPERTY_TYPE',
            'PROPERTY_URL',
            'PROPERTY_USER',
            'SORT',
            'TIMESTAMP_X',
        ];
    }

    protected function getDefaultOrder(): array
    {
        return [
            'timestamp_x' => 'desc',
            'id' => 'asc',
        ];
    }

    private function getQuestionType(int $enumIdentifier): QuestionType
    {
        $questionType = $this->getQuestionTypeIndex()[$enumIdentifier] ?? null;

        if (null === $questionType) {
            throw new \RuntimeException(sprintf('Unknown question type %d.', $enumIdentifier));
        }

        return $questionType;
    }

    /**
     * @return array<int,QuestionType>
     */
    private function getQuestionTypeIndex(): array
    {
        $result = $this->questionTypeIndex;

        if (null === $result) {
            $result = [];

            foreach (QuestionType::cases() as $case) {
                $result[$this->properties->getEnumId($this->getIblockId(), 'TYPE', $case->name)] = $case;
            }
        }

        return $result;
    }
}
