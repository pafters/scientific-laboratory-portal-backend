<?php

declare(strict_types=1);

use Bitrix\Iblock\IblockTable;

return static function (): void {
    $fields = [
        'ACTIVE_FROM' => [
            'DEFAULT_VALUE' => '=today',
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

    $manager = new CIBlock();
    $result = $manager->Add([
        'ACTIVE' => 'Y',
        'API_CODE' => 'News',
        'BIZPROC' => 'N',
        'CANONICAL_PAGE_URL' => '',
        'CODE' => 'news',
        'DESCRIPTION' => '',
        'DESCRIPTION_TYPE' => 'text',
        'DETAIL_PAGE_URL' => '#SITE_DIR#/news/#ELEMENT_CODE#/',
        'EDIT_FILE_AFTER' => '',
        'EDIT_FILE_BEFORE' => '',
        'ELEMENTS_NAME' => 'Новости',
        'ELEMENT_ADD' => 'Добавить новость',
        'ELEMENT_DELETE' => 'Удалить новость',
        'ELEMENT_EDIT' => 'Изменить новость',
        'ELEMENT_NAME' => 'Новость',
        'FIELDS' => $fields,
        'GROUP_ID' => [1 => 'X', 2 => 'R'],
        'IBLOCK_TYPE_ID' => 'content',
        'INDEX_ELEMENT' => 'N',
        'INDEX_SECTION' => 'N',
        'LID' => 's1',
        'LIST_MODE' => 'S',
        'LIST_PAGE_URL' => '#SITE_DIR#/news/',
        'NAME' => 'Новости',
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
        'SECTION_PAGE_URL' => '#SITE_DIR#/news/#SECTION_CODE_PATH#/',
        'SECTION_PROPERTY' => 'N',
        'SORT' => 10,
        'VERSION' => 2,
        'WORKFLOW' => 'N',
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create news iblock. '.Phosagro\get_bitrix_error());
    }

    $manager = new CIBlockProperty();

    $result = $manager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'CITY',
        'FILTRABLE' => 'Y',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'News'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'Y',
        'LINK_IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'City'], 'select' => ['ID']])['ID'],
        'MULTIPLE' => 'N',
        'NAME' => 'Город',
        'PROPERTY_TYPE' => 'E',
        'SEARCHABLE' => 'N',
        'SORT' => 10,
        'WITH_DESCRIPTION' => 'N',
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create news city property. '.Phosagro\get_bitrix_error());
    }

    $result = $manager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'PHOTOS',
        'FILE_TYPE' => 'gif, jpg, jpeg, png, webp',
        'FILTRABLE' => 'N',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'News'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'N',
        'MULTIPLE' => 'Y',
        'NAME' => 'Фотографии',
        'PROPERTY_TYPE' => 'F',
        'SEARCHABLE' => 'N',
        'SORT' => 20,
        'WITH_DESCRIPTION' => 'Y',
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create news photos property. '.Phosagro\get_bitrix_error());
    }
};
