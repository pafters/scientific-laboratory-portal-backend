<?php

declare(strict_types=1);

return static function (): void {
    $iblockManager = new CIBlock();

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
            'IS_REQUIRED' => 'N',
        ],
        'DETAIL_TEXT' => [
            'IS_REQUIRED' => 'N',
        ],
        'PREVIEW_TEXT' => [
            'IS_REQUIRED' => 'N',
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
        'API_CODE' => 'Video',
        'BIZPROC' => 'N',
        'CANONICAL_PAGE_URL' => '',
        'CODE' => 'video',
        'DESCRIPTION' => '',
        'DESCRIPTION_TYPE' => 'text',
        'DETAIL_PAGE_URL' => '#SITE_DIR#/video/get/#ELEMENT_ID#/',
        'EDIT_FILE_AFTER' => '',
        'EDIT_FILE_BEFORE' => '',
        'ELEMENTS_NAME' => 'Видео',
        'ELEMENT_ADD' => 'Добавить видео',
        'ELEMENT_DELETE' => 'Удалить видео',
        'ELEMENT_EDIT' => 'Изменить видео',
        'ELEMENT_NAME' => 'Видео',
        'FIELDS' => $fields,
        'GROUP_ID' => [1 => 'X', 2 => 'R'],
        'IBLOCK_TYPE_ID' => 'content',
        'INDEX_ELEMENT' => 'N',
        'INDEX_SECTION' => 'N',
        'LID' => 's1',
        'LIST_MODE' => 'S',
        'LIST_PAGE_URL' => '#SITE_DIR#/video/find/',
        'NAME' => 'Видео',
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
        'SECTION_PAGE_URL' => '#SITE_DIR#/video/find/',
        'SECTION_PROPERTY' => 'N',
        'SORT' => 40,
        'VERSION' => 2,
        'WORKFLOW' => 'N',
    ]);

    if (!$iblockId) {
        throw new RuntimeException('Can not create iblock. '.$iblockManager->LAST_ERROR);
    }
};
