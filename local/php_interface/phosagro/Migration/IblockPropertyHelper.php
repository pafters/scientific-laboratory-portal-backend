<?php

declare(strict_types=1);

namespace Phosagro\Migration;

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Phosagro\System\Injector;

final class IblockPropertyHelper
{
    private readonly IblockPropertyEnumHelper $enumHelper;
    private readonly \CIBlockProperty $propertyManager;

    public function __construct(
        private readonly DatabaseHelper $databaseHelper,
        private readonly IblockHelper $iblockHelper,
    ) {
        $this->propertyManager = new \CIBlockProperty();
    }

    public function createPropertyBool(
        string $type,
        string $iblock,
        string $property,
        string $name,
        array $fields = [],
    ): void {
        $this->createProperty($type, $iblock, $property, $name, false, array_replace_recursive([
            'LIST_TYPE' => 'C',
            'PROPERTY_TYPE' => 'L',
        ], $fields));
        $this->enumHelper->createIblockPropertyEnum($type, $iblock, $property, 'Y', 'Да');
    }

    public function createPropertyDate(
        string $type,
        string $iblock,
        string $property,
        string $name,
        bool $required = false,
        array $fields = [],
    ): void {
        $this->createProperty($type, $iblock, $property, $name, $required, array_replace_recursive([
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'DateTime',
        ], $fields));
    }

    public function createPropertyElement(
        string $type,
        string $iblock,
        string $property,
        string $name,
        string $linkType,
        string $linkIblock,
        bool $required = false,
        array $fields = [],
    ): void {
        $this->createProperty($type, $iblock, $property, $name, $required, array_replace_recursive([
            'PROPERTY_TYPE' => 'E',
            'LINK_IBLOCK_ID' => $this->iblockHelper->getIblockId($linkType, $linkIblock),
        ], $fields));
    }

    public function createPropertyEnum(
        string $type,
        string $iblock,
        string $property,
        string $name,
        bool $required = false,
        array $fields = [],
    ): void {
        $this->createProperty($type, $iblock, $property, $name, $required, array_replace_recursive([
            'PROPERTY_TYPE' => 'L',
        ], $fields));
    }

    public function createPropertyFile(
        string $type,
        string $iblock,
        string $property,
        string $name,
        bool $required = false,
        array $fields = [],
    ): void {
        $this->createProperty($type, $iblock, $property, $name, $required, array_replace_recursive([
            'PROPERTY_TYPE' => 'F',
        ], $fields));
    }

    public function createPropertyNumber(
        string $type,
        string $iblock,
        string $property,
        string $name,
        bool $required = false,
        array $fields = [],
    ): void {
        $this->createProperty($type, $iblock, $property, $name, $required, array_replace_recursive([
            'PROPERTY_TYPE' => 'N',
        ], $fields));
    }

    public function createPropertyString(
        string $type,
        string $iblock,
        string $property,
        string $name,
        bool $required = false,
        array $fields = [],
    ): void {
        $this->createProperty($type, $iblock, $property, $name, $required, array_replace_recursive([
            'PROPERTY_TYPE' => 'S',
        ], $fields));
    }

    public function createPropertyText(
        string $type,
        string $iblock,
        string $property,
        string $name,
        bool $required = false,
        array $fields = [],
    ): void {
        $this->createProperty($type, $iblock, $property, $name, $required, array_replace_recursive([
            'COL_COUNT' => 50,
            'LIST_TYPE' => 'S',
            'ROW_COUNT' => 5,
        ], $fields));
        $this->enumHelper->createIblockPropertyEnum($type, $iblock, $property, 'Y', 'Да');
    }

    public function createPropertyUser(
        string $type,
        string $iblock,
        string $property,
        string $name,
        bool $required = false,
        array $fields = [],
    ): void {
        $this->createProperty($type, $iblock, $property, $name, $required, array_replace_recursive([
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'UserID',
        ], $fields));
    }

    public function createPropertyWebForm(
        string $type,
        string $iblock,
        string $property,
        string $name,
        bool $required = false,
        array $fields = [],
    ): void {
        $this->createProperty($type, $iblock, $property, $name, $required, array_replace_recursive([
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'WebForm',
        ], $fields));
    }

    public function createPropertyWebFormResult(
        string $type,
        string $iblock,
        string $property,
        string $name,
        bool $required = false,
        array $fields = [],
    ): void {
        $this->createProperty($type, $iblock, $property, $name, $required, array_replace_recursive([
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'WebFormResult',
        ], $fields));
    }

    public function deleteProperty(
        string $type,
        string $iblock,
        string $property,
    ): void {
        $this->databaseHelper->assertSuccess(
            $this->propertyManager->Delete(
                $this->getPropertyId($type, $iblock, $property),
            ),
            'iblock property',
            "{$type}/{$iblock}/{$property}",
            'delete',
            $this->propertyManager->LAST_ERROR,
        );
    }

    public function getPropertyId(
        string $type,
        string $iblock,
        string $property,
    ): int {
        return $this->databaseHelper->fetchSingleId(PropertyTable::getList([
            'filter' => [
                '=CODE' => $property,
                '=IBLOCK_ID' => $this->iblockHelper->getIblockId($type, $iblock),
            ],
            'limit' => 2,
            'select' => [
                'ID',
            ],
        ]), 'iblock property', "{$type}/{$iblock}/{$property}");
    }

    #[Injector]
    public function setEnumHelper(IblockPropertyEnumHelper $enumHelper): void
    {
        $this->enumHelper = $enumHelper;
    }

    public function updateProperty(
        string $type,
        string $iblock,
        string $property,
        array $fields = [],
    ): void {
        $this->databaseHelper->assertSuccess(
            $this->propertyManager->Update(
                $this->getPropertyId($type, $iblock, $property),
                $fields,
            ),
            'iblock property',
            "{$type}/{$iblock}/{$property}",
            'update',
            $this->propertyManager->LAST_ERROR,
        );
    }

    private function createProperty(
        string $type,
        string $iblock,
        string $property,
        string $name,
        bool $required = false,
        array $fields = [],
    ): void {
        $this->databaseHelper->assertSuccess(
            $this->propertyManager->Add(array_replace_recursive([
                'ACTIVE' => 'Y',
                'CODE' => $property,
                'COL_COUNT' => 50,
                'DEFAULT_VALUE' => null,
                'FILE_TYPE' => null,
                'FILTRABLE' => 'Y',
                'HINT' => null,
                'IBLOCK_ID' => $this->iblockHelper->getIblockId($type, $iblock),
                'IS_REQUIRED' => $required ? 'Y' : 'N',
                'LINK_IBLOCK_ID' => null,
                'LIST_TYPE' => 'L',
                'MULTIPLE' => 'N',
                'MULTIPLE_CNT' => null,
                'NAME' => $name,
                'PROPERTY_TYPE' => 'S',
                'ROW_COUNT' => 1,
                'SEARCHABLE' => 'N',
                'SORT' => $this->getNextSort($type, $iblock),
                'USER_TYPE' => null,
                'USER_TYPE_SETTINGS' => null,
                'WITH_DESCRIPTION' => 'N',
                'XML_ID' => "{$type}_{$iblock}_{$property}",
            ], $fields)),
            'iblock property',
            "{$type}/{$iblock}/{$property}",
            'create',
            $this->propertyManager->LAST_ERROR,
        );
    }

    private function getNextSort(string $type, string $iblock): int
    {
        return $this->databaseHelper->fetchSingleInt(PropertyTable::getList([
            'filter' => [
                '=IBLOCK_ID' => $this->iblockHelper->getIblockId($type, $iblock),
            ],
            'select' => [
                new ExpressionField('MAX_SORT', 'max(SORT)'),
            ],
        ]), 'iblock property', '', 'MAX_SORT') + 10;
    }
}
