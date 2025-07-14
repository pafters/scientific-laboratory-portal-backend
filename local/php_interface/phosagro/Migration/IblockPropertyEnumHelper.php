<?php

declare(strict_types=1);

namespace Phosagro\Migration;

use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\ORM\Fields\ExpressionField;
use CIBlockPropertyEnum;

final class IblockPropertyEnumHelper
{
    private readonly \CIBlockPropertyEnum $enumManager;

    public function __construct(
        private readonly DatabaseHelper $databaseHelper,
        private readonly IblockPropertyHelper $propertyHelper,
    ) {
        $this->enumManager = new CIBlockPropertyEnum();
    }

    public function createIblockPropertyEnum(
        string $type,
        string $iblock,
        string $property,
        string $enum,
        string $name,
        bool $default = false,
        ?int $sort = null,
    ): void {
        $this->databaseHelper->assertSuccess($this->enumManager->Add([
            'DEF' => $default ? 'Y' : 'N',
            'PROPERTY_ID' => $this->propertyHelper->getPropertyId($type, $iblock, $property),
            'SORT' => $sort ?? $this->getNextSort($type, $iblock, $property),
            'VALUE' => $name,
            'XML_ID' => $enum,
        ]), 'iblock property enum', "{$type}/{$iblock}/{$property}/{$enum}", 'create');
    }

    private function getNextSort(string $type, string $iblock, string $property): int
    {
        return $this->databaseHelper->fetchSingleInt(PropertyEnumerationTable::getList([
            'filter' => [
                '=PROPERTY_ID' => $this->propertyHelper->getPropertyId($type, $iblock, $property),
            ],
            'select' => [
                new ExpressionField('MAX_SORT', 'max(SORT)'),
            ],
        ]), 'iblock property', '', 'MAX_SORT') + 10;
    }
}
