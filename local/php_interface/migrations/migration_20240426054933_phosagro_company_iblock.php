<?php

declare(strict_types=1);

use Bitrix\Iblock\IblockTable;

return static function (): void {
    $iblockManager = new CIBlock();

    $iblockId = $iblockManager->Add([
        'ACTIVE' => 'Y',
        'API_CODE' => 'PhosagroCompany',
        'BIZPROC' => 'N',
        'CANONICAL_PAGE_URL' => '',
        'CODE' => 'phosagro_company',
        'DESCRIPTION' => '',
        'DESCRIPTION_TYPE' => 'text',
        'DETAIL_PAGE_URL' => '',
        'EDIT_FILE_AFTER' => '',
        'EDIT_FILE_BEFORE' => '',
        'ELEMENTS_NAME' => 'Компании Фосагро',
        'ELEMENT_ADD' => 'Добавить компанию Фосагро',
        'ELEMENT_DELETE' => 'Удалить компанию Фосагро',
        'ELEMENT_EDIT' => 'Изменить компанию Фосагро',
        'ELEMENT_NAME' => 'Компания Фосагро',
        'GROUP_ID' => [1 => 'X', 2 => 'R'],
        'IBLOCK_TYPE_ID' => 'directory',
        'INDEX_ELEMENT' => 'N',
        'INDEX_SECTION' => 'N',
        'LID' => 's1',
        'LIST_MODE' => 'S',
        'LIST_PAGE_URL' => '',
        'NAME' => 'Компании Фосагро',
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
        'SORT' => 90,
        'VERSION' => 2,
        'WORKFLOW' => 'N',
    ]);

    if (!$iblockId) {
        throw new RuntimeException('Can not create iblock. '.$iblockManager->LAST_ERROR);
    }

    $propertyManager = new CIBlockProperty();

    $propertyId = $propertyManager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'OWNER',
        'FILTRABLE' => 'Y',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'PhosagroCompany'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'Y',
        'MULTIPLE' => 'N',
        'NAME' => 'Владелец',
        'PROPERTY_TYPE' => 'S',
        'SEARCHABLE' => 'N',
        'SORT' => 10,
        'USER_TYPE' => 'UserID',
        'WITH_DESCRIPTION' => 'N',
    ]);

    if (!$propertyId) {
        throw new RuntimeException('Can not create property. '.$propertyManager->LAST_ERROR);
    }
};
