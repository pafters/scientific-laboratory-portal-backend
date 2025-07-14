<?php

declare(strict_types=1);

namespace Phosagro\Migration;

use Bitrix\Iblock\TypeTable;
use Bitrix\Main\ORM\Fields\ExpressionField;

final class IblockTypeHelper
{
    private readonly \CIBlockType $typeManager;

    public function __construct(
        private readonly DatabaseHelper $databaseHelper,
    ) {
        $this->typeManager = new \CIBlockType();
    }

    public function createIblockType(
        string $type,
        string $nameEn,
        string $nameRu,
        bool $sections = false,
    ): void {
        $en = [
            'ELEMENT_NAME' => 'Elements',
            'NAME' => $nameEn,
            'SECTION_NAME' => 'Sections',
        ];

        $ru = [
            'ELEMENT_NAME' => 'Элементы',
            'NAME' => $nameRu,
            'SECTION_NAME' => 'Разделы',
        ];

        $this->databaseHelper->assertSuccess($this->typeManager->Add([
            'EDIT_FILE_AFTER' => '',
            'EDIT_FILE_BEFORE' => '',
            'ID' => $type,
            'IN_RSS' => 'N',
            'LANG' => ['en' => $en, 'ru' => $ru],
            'SECTIONS' => $sections ? 'Y' : 'N',
            'SORT' => $this->getNextSort(),
        ]), 'iblock type', $type, 'create', $this->typeManager->LAST_ERROR);
    }

    private function getNextSort(): int
    {
        return $this->databaseHelper->fetchSingleInt(TypeTable::getList([
            'select' => [
                new ExpressionField('MAX_SORT', 'max(SORT)'),
            ],
        ]), 'iblock type', '', 'MAX_SORT') + 10;
    }
}
