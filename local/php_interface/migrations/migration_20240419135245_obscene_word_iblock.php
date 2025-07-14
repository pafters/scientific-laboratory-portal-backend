<?php

declare(strict_types=1);

use Bitrix\Iblock\IblockTable;

return static function (): void {
    $manager = new CIBlock();
    $result = $manager->Add([
        'ACTIVE' => 'Y',
        'API_CODE' => 'ObsceneWord',
        'BIZPROC' => 'N',
        'CANONICAL_PAGE_URL' => '',
        'CODE' => 'obscene_word',
        'DESCRIPTION' => '',
        'DESCRIPTION_TYPE' => 'text',
        'DETAIL_PAGE_URL' => '',
        'EDIT_FILE_AFTER' => '',
        'EDIT_FILE_BEFORE' => '',
        'ELEMENTS_NAME' => 'Нецензурные слова',
        'ELEMENT_ADD' => 'Добавить нецензурное слово',
        'ELEMENT_DELETE' => 'Удалить нецензурное слово',
        'ELEMENT_EDIT' => 'Изменить нецензурное слово',
        'ELEMENT_NAME' => 'Нецензурное слово',
        'GROUP_ID' => [1 => 'X', 2 => 'R'],
        'IBLOCK_TYPE_ID' => 'directory',
        'INDEX_ELEMENT' => 'N',
        'INDEX_SECTION' => 'N',
        'LID' => 's1',
        'LIST_MODE' => 'S',
        'LIST_PAGE_URL' => '',
        'NAME' => 'Нецензурные слова',
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
        'SORT' => 30,
        'VERSION' => 2,
        'WORKFLOW' => 'N',
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create obscene word iblock. '.Phosagro\get_bitrix_error());
    }

    $manager = new CIBlockProperty();
    $result = $manager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'OWNER',
        'FILTRABLE' => 'Y',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'ObsceneWord'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'Y',
        'MULTIPLE' => 'N',
        'NAME' => 'Владелец',
        'PROPERTY_TYPE' => 'S',
        'SEARCHABLE' => 'N',
        'SORT' => 10,
        'WITH_DESCRIPTION' => 'N',
        'USER_TYPE' => 'UserID',
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create obscene word owner property. '.Phosagro\get_bitrix_error());
    }
};
