<?php

declare(strict_types=1);

use Bitrix\Iblock\IblockTable;

return static function (): void {

    $fields = [
        'CODE' => [
            'DEFAULT_VALUE' => [
                'TRANSLITERATION' => 'Y',
                'UNIQUE' => 'Y',
            ],
            'IS_REQUIRED' => 'Y',
        ],
        'PREVIEW_PICTURE' => [
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


    // Создаем инфоблок "Контакты"
    $manager = new CIBlock();

    $result = $manager->Add([
        'ACTIVE' => 'Y',
        'API_CODE' => 'Contacts',
        'BIZPROC' => 'N',
        'CANONICAL_PAGE_URL' => '',
        'CODE' => 'contacts',
        'DESCRIPTION' => '',
        'DESCRIPTION_TYPE' => 'text',
        'DETAIL_PAGE_URL' => '#SITE_DIR#/contacts/#ELEMENT_CODE#/',
        'EDIT_FILE_AFTER' => '',
        'EDIT_FILE_BEFORE' => '',
        'ELEMENTS_NAME' => 'Контакты',
        'ELEMENT_ADD' => 'Добавить контакт',
        'ELEMENT_DELETE' => 'Удалить контакт',
        'ELEMENT_EDIT' => 'Изменить контакт',
        'ELEMENT_NAME' => 'Контакт',
        'FIELDS' => $fields,
        'GROUP_ID' => [1 => 'X', 2 => 'R'],
        'IBLOCK_TYPE_ID' => 'content',
        'INDEX_ELEMENT' => 'N',
        'INDEX_SECTION' => 'N',
        'LID' => 's1',
        'LIST_MODE' => 'S',
        'LIST_PAGE_URL' => '#SITE_DIR#/contacts/',
        'NAME' => 'Контакты',
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
        'SECTION_PAGE_URL' => '#SITE_DIR#/contacts/#SECTION_CODE_PATH#/',
        'SECTION_PROPERTY' => 'N',
        'SORT' => 50,
        'VERSION' => 2,
        'WORKFLOW' => 'N',
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create Contacts infoblock. ' . $manager->LAST_ERROR);
    }

    // Создаем свойство "Город"
    $manager = new CIBlockProperty();

    $result = $manager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'CITY_CATEGORY',
        'FILTRABLE' => 'Y',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'Contacts'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'Y',
        'LINK_IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'City'], 'select' => ['ID']])['ID'],
        'NAME' => 'Город',
        'PROPERTY_TYPE' => 'E',
        'MULTIPLE' => 'N',
        'SEARCHABLE' => 'N',
        'SORT' => 10,
        'WITH_DESCRIPTION' => 'N',
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create CITY_CATEGORY in the Contacts infoblock. ' . $manager->LAST_ERROR);
    }

    // Создаем свойство "Широта"
    $manager = new CIBlockProperty();

    $result = $manager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'PROPERTY_LATITUDE',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'Contacts'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'Y',
        'NAME' => 'Широта',
        'PROPERTY_TYPE' => 'S',
        'MULTIPLE' => 'N',
        'SEARCHABLE' => 'N',
        'SORT' => 20,
        'WITH_DESCRIPTION' => 'N',
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create PROPERTY_LATITUDE in the Contacts infoblock. ' . $manager->LAST_ERROR);
    }

    // Создаем свойство "Долгота"
    $manager = new CIBlockProperty();

    $result = $manager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'PROPERTY_LONGITUDE',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'Contacts'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'Y',
        'NAME' => 'Долгота',
        'PROPERTY_TYPE' => 'S',
        'MULTIPLE' => 'N',
        'SEARCHABLE' => 'N',
        'SORT' => 30,
        'WITH_DESCRIPTION' => 'N',
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create PROPERTY_LONGITUDE in the Contacts infoblock. ' . $manager->LAST_ERROR);
    }

    // Создаем свойство "Телефон"
    $manager = new CIBlockProperty();

    $result = $manager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'PROPERTY_PHONE',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'Contacts'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'Y',
        'NAME' => 'Телефон',
        'PROPERTY_TYPE' => 'S',
        'MULTIPLE' => 'N',
        'SEARCHABLE' => 'N',
        'SORT' => 40,
        'WITH_DESCRIPTION' => 'N',
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create PROPERTY_PHONE in the Contacts infoblock. ' . $manager->LAST_ERROR);
    }

    // Создаем свойство "Сайт"
    $manager = new CIBlockProperty();

    $result = $manager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'PROPERTY_SITE',
        'FILTRABLE' => 'Y',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'Contacts'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'Y',
        'NAME' => 'Сайт',
        'PROPERTY_TYPE' => 'S',
        'MULTIPLE' => 'N',
        'SEARCHABLE' => 'N',
        'SORT' => 50,
        'WITH_DESCRIPTION' => 'N',
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create PROPERTY_SITE in the Contacts infoblock. ' . $manager->LAST_ERROR);
    }

    // Создаем свойство "График работы"
    $manager = new CIBlockProperty();

    $result = $manager->Add([
        'ACTIVE' => 'Y',
        'FILTRABLE' => 'Y',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'Contacts'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'Y',
        'NAME' => 'График работы',
        'CODE' => 'PROPERTY_SCHEDULE',
        'PROPERTY_TYPE' => 'S',
        'MULTIPLE' => 'Y',
        'MULTIPLE_CNT' => 1,
        'WITH_DESCRIPTION' => 'Y',
        'SEARCHABLE' => 'N',
        'SORT' => 60,
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create PROPERTY_SCHEDULE in the Contacts infoblock. ' . $manager->LAST_ERROR);
    }
};
