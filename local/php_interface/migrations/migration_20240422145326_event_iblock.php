<?php

declare(strict_types=1);

use Bitrix\Iblock\IblockTable;

return static function (): void {
    $iblockManager = new CIBlock();
    $propertyManager = new CIBlockProperty();
    $enumManager = new CIBlockPropertyEnum();

    $fields = [
        'ACTIVE_FROM' => [
            'IS_REQUIRED' => 'Y',
        ],
        'CODE' => [
            'DEFAULT_VALUE' => [
                'TRANSLITERATION' => 'Y',
                'UNIQUE' => 'Y',
            ],
            'IS_REQUIRED' => 'Y',
        ],
        'DETAIL_PICTURE' => [
            'IS_REQUIRED' => 'Y',
        ],
        'DETAIL_TEXT' => [
            'IS_REQUIRED' => 'Y',
        ],
        'PREVIEW_TEXT' => [
            'IS_REQUIRED' => 'Y',
        ],
        'SECTION_CODE' => [
            'DEFAULT_VALUE' => [
                'TRANSLITERATION' => 'Y',
                'UNIQUE' => 'Y',
            ],
            'IS_REQUIRED' => 'Y',
        ],
    ];

    $iblockId = $iblockManager->Add([
        'ACTIVE' => 'Y',
        'API_CODE' => 'Event',
        'BIZPROC' => 'N',
        'CANONICAL_PAGE_URL' => '',
        'CODE' => 'event',
        'DESCRIPTION' => '',
        'DESCRIPTION_TYPE' => 'text',
        'DETAIL_PAGE_URL' => '#SITE_DIR#/event/get/#ELEMENT_ID#/',
        'EDIT_FILE_AFTER' => '',
        'EDIT_FILE_BEFORE' => '',
        'ELEMENTS_NAME' => 'События',
        'ELEMENT_ADD' => 'Добавить событие',
        'ELEMENT_DELETE' => 'Удалить событие',
        'ELEMENT_EDIT' => 'Изменить событие',
        'ELEMENT_NAME' => 'Событие',
        'FIELDS' => $fields,
        'GROUP_ID' => [1 => 'X', 2 => 'R'],
        'IBLOCK_TYPE_ID' => 'content',
        'INDEX_ELEMENT' => 'N',
        'INDEX_SECTION' => 'N',
        'LID' => 's1',
        'LIST_MODE' => 'S',
        'LIST_PAGE_URL' => '#SITE_DIR#/event/find/',
        'NAME' => 'События',
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
        'SECTION_PAGE_URL' => '#SITE_DIR#/event/find/',
        'SECTION_PROPERTY' => 'N',
        'SORT' => 30,
        'VERSION' => 2,
        'WORKFLOW' => 'N',
    ]);

    if (!$iblockId) {
        throw new RuntimeException('Can not create iblock. '.$iblockManager->LAST_ERROR);
    }

    $propertyId = $propertyManager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'ARCHIVED',
        'FILTRABLE' => 'Y',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'Event'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'N',
        'MULTIPLE' => 'N',
        'NAME' => 'В архиве',
        'PROPERTY_TYPE' => 'L',
        'SEARCHABLE' => 'N',
        'SORT' => 10,
        'WITH_DESCRIPTION' => 'N',
    ]);

    if (!$propertyId) {
        throw new RuntimeException('Can not create property. '.$propertyManager->LAST_ERROR);
    }

    $enumId = $enumManager->Add([
        'DEF' => 'N',
        'PROPERTY_ID' => $propertyId,
        'SORT' => 10,
        'VALUE' => 'Да',
        'XML_ID' => 'Y',
    ]);

    if (!$enumId) {
        throw new RuntimeException('Can not create enum. '.Phosagro\get_bitrix_error());
    }

    $propertyId = $propertyManager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'MODERATOR',
        'FILTRABLE' => 'Y',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'Event'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'Y',
        'MULTIPLE' => 'N',
        'NAME' => 'Модератор',
        'PROPERTY_TYPE' => 'S',
        'SEARCHABLE' => 'N',
        'SORT' => 20,
        'USER_TYPE' => 'UserID',
        'WITH_DESCRIPTION' => 'N',
    ]);

    if (!$propertyId) {
        throw new RuntimeException('Can not create property. '.$propertyManager->LAST_ERROR);
    }

    $propertyId = $propertyManager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'POINTS',
        'FILTRABLE' => 'Y',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'Event'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'N',
        'MULTIPLE' => 'N',
        'NAME' => 'Баллы',
        'PROPERTY_TYPE' => 'N',
        'SEARCHABLE' => 'N',
        'SORT' => 30,
        'WITH_DESCRIPTION' => 'N',
    ]);

    if (!$propertyId) {
        throw new RuntimeException('Can not create property. '.$propertyManager->LAST_ERROR);
    }

    $propertyId = $propertyManager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'CITY',
        'FILTRABLE' => 'Y',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'Event'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'N',
        'LINK_IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'City'], 'select' => ['ID']])['ID'],
        'MULTIPLE' => 'N',
        'NAME' => 'Город',
        'PROPERTY_TYPE' => 'E',
        'SEARCHABLE' => 'N',
        'SORT' => 40,
        'WITH_DESCRIPTION' => 'N',
    ]);

    if (!$propertyId) {
        throw new RuntimeException('Can not create property. '.$propertyManager->LAST_ERROR);
    }

    $propertyId = $propertyManager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'AGE_CATEGORY',
        'FILTRABLE' => 'Y',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'Event'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'Y',
        'LINK_IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'AgeCategory'], 'select' => ['ID']])['ID'],
        'MULTIPLE' => 'N',
        'NAME' => 'Возраст',
        'PROPERTY_TYPE' => 'E',
        'SEARCHABLE' => 'N',
        'SORT' => 50,
        'WITH_DESCRIPTION' => 'N',
    ]);

    if (!$propertyId) {
        throw new RuntimeException('Can not create property. '.$propertyManager->LAST_ERROR);
    }

    $propertyId = $propertyManager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'USER_GROUP',
        'FILTRABLE' => 'Y',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'Event'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'N',
        'LINK_IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'UserGroup'], 'select' => ['ID']])['ID'],
        'MULTIPLE' => 'N',
        'NAME' => 'Для кого',
        'PROPERTY_TYPE' => 'E',
        'SEARCHABLE' => 'N',
        'SORT' => 60,
        'WITH_DESCRIPTION' => 'N',
    ]);

    if (!$propertyId) {
        throw new RuntimeException('Can not create property. '.$propertyManager->LAST_ERROR);
    }

    $propertyId = $propertyManager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'PARTNER',
        'FILTRABLE' => 'Y',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'Event'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'N',
        'LINK_IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'Partner'], 'select' => ['ID']])['ID'],
        'MULTIPLE' => 'N',
        'NAME' => 'Партнёр',
        'PROPERTY_TYPE' => 'E',
        'SEARCHABLE' => 'N',
        'SORT' => 70,
        'WITH_DESCRIPTION' => 'N',
    ]);

    if (!$propertyId) {
        throw new RuntimeException('Can not create property. '.$propertyManager->LAST_ERROR);
    }

    $propertyId = $propertyManager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'GALLERY',
        'FILE_TYPE' => 'gif, jpg, jpeg, png, webp',
        'FILTRABLE' => 'N',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'Event'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'N',
        'MULTIPLE' => 'Y',
        'NAME' => 'Галерея',
        'PROPERTY_TYPE' => 'F',
        'SEARCHABLE' => 'N',
        'SORT' => 80,
        'WITH_DESCRIPTION' => 'Y',
    ]);

    if (!$propertyId) {
        throw new RuntimeException('Can not create property. '.$propertyManager->LAST_ERROR);
    }
};
