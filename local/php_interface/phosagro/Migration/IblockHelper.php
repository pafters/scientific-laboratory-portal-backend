<?php

declare(strict_types=1);

namespace Phosagro\Migration;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Phosagro\Util\Text;

final class IblockHelper
{
    private readonly \CIBlock $iblockManager;

    public function __construct(
        private readonly DatabaseHelper $databaseHelper,
    ) {
        $this->iblockManager = new \CIBlock();
    }

    public function createIblock(
        string $type,
        string $iblock,
        string $name,
        string $nameA,
        string $nameM,
        array $fields = [],
    ): void {
        $this->databaseHelper->assertSuccess($this->iblockManager->Add(array_replace_recursive([
            'ACTIVE' => 'Y',
            'API_CODE' => $iblock,
            'BIZPROC' => 'N',
            'CANONICAL_PAGE_URL' => '',
            'CODE' => Text::snake($iblock),
            'DESCRIPTION' => '',
            'DESCRIPTION_TYPE' => 'text',
            'DETAIL_PAGE_URL' => '',
            'EDIT_FILE_AFTER' => '',
            'EDIT_FILE_BEFORE' => '',
            'ELEMENTS_NAME' => $nameM,
            'ELEMENT_ADD' => 'Добавить '.Text::lower($nameA),
            'ELEMENT_DELETE' => 'Удалить '.Text::lower($nameA),
            'ELEMENT_EDIT' => 'Изменить '.Text::lower($nameA),
            'ELEMENT_NAME' => $name,
            'FIELDS' => [],
            'GROUP_ID' => [1 => 'X', 2 => 'R'],
            'IBLOCK_TYPE_ID' => $type,
            'INDEX_ELEMENT' => 'N',
            'INDEX_SECTION' => 'N',
            'LID' => 's1',
            'LIST_MODE' => 'S',
            'LIST_PAGE_URL' => '',
            'NAME' => $nameM,
            'PICTURE' => '',
            'PROPERTY_INDEX' => 'N',
            'RIGHTS_MODE' => 'S',
            'RSS_ACTIVE' => 'N',
            'RSS_FILE_ACTIVE' => 'N',
            'RSS_TTL' => 24,
            'RSS_YANDEX_ACTIVE' => 'N',
            'SECTIONS_NAME' => 'Разделы',
            'SECTION_ADD' => 'Добавить раздел',
            'SECTION_CHOOSER' => 'L',
            'SECTION_DELETE' => 'Удалить раздел',
            'SECTION_EDIT' => 'Изменить раздел',
            'SECTION_NAME' => 'Раздел',
            'SECTION_PAGE_URL' => '',
            'SECTION_PROPERTY' => 'N',
            'SORT' => $this->getNextSort($type),
            'VERSION' => 2,
            'WORKFLOW' => 'N',
        ], $fields)), 'iblock', $iblock, 'create', $this->iblockManager->LAST_ERROR);
    }

    public function getIblockId(string $type, string $iblock): int
    {
        return $this->databaseHelper->fetchSingleId(IblockTable::getList([
            'filter' => [
                '=API_CODE' => $iblock,
                '=IBLOCK_TYPE_ID' => $type,
            ],
            'limit' => 2,
            'select' => [
                'ID',
            ],
        ]), 'iblock', $iblock);
    }

    public function updateIblock(
        string $type,
        string $iblock,
        array $fields = [],
    ): void {
        $this->databaseHelper->assertSuccess($this->iblockManager->Update(
            $this->getIblockId($type, $iblock),
            $fields,
        ), 'iblock', $iblock, 'update', $this->iblockManager->LAST_ERROR);
    }

    private function getNextSort(string $type): int
    {
        return $this->databaseHelper->fetchSingleInt(IblockTable::getList([
            'filter' => [
                '=IBLOCK_TYPE_ID' => $type,
            ],
            'select' => [
                new ExpressionField('MAX_SORT', 'max(SORT)'),
            ],
        ]), 'iblock', '', 'MAX_SORT') + 10;
    }
}
