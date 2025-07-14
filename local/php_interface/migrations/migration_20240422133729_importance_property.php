<?php

declare(strict_types=1);

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Application;

return static function (): void {
    $db = Application::getConnection();
    $db->startTransaction();

    try {
        $enumManager = new CIBlockPropertyEnum();
        $propertyManager = new CIBlockProperty();

        $propertyId = $propertyManager->Add([
            'ACTIVE' => 'Y',
            'CODE' => 'IMPORTANT',
            'FILTRABLE' => 'Y',
            'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'News'], 'select' => ['ID']])['ID'],
            'IS_REQUIRED' => 'N',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Важно',
            'PROPERTY_TYPE' => 'L',
            'SEARCHABLE' => 'N',
            'SORT' => 30,
            'USER_TYPE' => '',
            'WITH_DESCRIPTION' => 'N',
        ]);

        if (!$propertyId) {
            throw new RuntimeException('Can not create news important property. '.$propertyManager->LAST_ERROR);
        }

        $enumId = $enumManager->Add([
            'DEF' => 'N',
            'PROPERTY_ID' => $propertyId,
            'SORT' => 10,
            'VALUE' => 'Да',
            'XML_ID' => 'Y',
        ]);

        if (!$enumId) {
            throw new RuntimeException('Can not create news important enum. '.\Phosagro\get_bitrix_error());
        }

        $propertyId = $propertyManager->Add([
            'ACTIVE' => 'Y',
            'CODE' => 'IMPORTANT',
            'FILTRABLE' => 'Y',
            'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'Course'], 'select' => ['ID']])['ID'],
            'IS_REQUIRED' => 'N',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Важно',
            'PROPERTY_TYPE' => 'L',
            'SEARCHABLE' => 'N',
            'SORT' => 30,
            'USER_TYPE' => '',
            'WITH_DESCRIPTION' => 'N',
        ]);

        if (!$propertyId) {
            throw new RuntimeException('Can not create course important property. '.$propertyManager->LAST_ERROR);
        }

        $enumId = $enumManager->Add([
            'DEF' => 'N',
            'PROPERTY_ID' => $propertyId,
            'SORT' => 10,
            'VALUE' => 'Да',
            'XML_ID' => 'Y',
        ]);

        if (!$enumId) {
            throw new RuntimeException('Can not create course important enum. '.\Phosagro\get_bitrix_error());
        }
    } catch (Throwable $error) {
        $db->rollbackTransaction();

        throw $error;
    }

    $db->commitTransaction();
};
