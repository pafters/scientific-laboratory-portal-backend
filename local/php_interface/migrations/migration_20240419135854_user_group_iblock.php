<?php

declare(strict_types=1);

use Bitrix\Iblock\IblockTable;

return static function (): void {
    $manager = new CIBlock();
    $result = $manager->Add([
        'ACTIVE' => 'Y',
        'API_CODE' => 'UserGroup',
        'BIZPROC' => 'N',
        'CANONICAL_PAGE_URL' => '',
        'CODE' => 'user_group',
        'DESCRIPTION' => '',
        'DESCRIPTION_TYPE' => 'text',
        'DETAIL_PAGE_URL' => '',
        'EDIT_FILE_AFTER' => '',
        'EDIT_FILE_BEFORE' => '',
        'ELEMENTS_NAME' => 'Группы пользователей',
        'ELEMENT_ADD' => 'Добавить группу пользователей',
        'ELEMENT_DELETE' => 'Удалить группу пользователей',
        'ELEMENT_EDIT' => 'Изменить группу пользователей',
        'ELEMENT_NAME' => 'Группа пользователей',
        'GROUP_ID' => [1 => 'X', 2 => 'R'],
        'IBLOCK_TYPE_ID' => 'directory',
        'INDEX_ELEMENT' => 'N',
        'INDEX_SECTION' => 'N',
        'LID' => 's1',
        'LIST_MODE' => 'S',
        'LIST_PAGE_URL' => '',
        'NAME' => 'Группы пользователей',
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
        'SORT' => 60,
        'VERSION' => 2,
        'WORKFLOW' => 'N',
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create user group iblock. '.Phosagro\get_bitrix_error());
    }

    $manager = new CIBlockProperty();
    $result = $manager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'OWNER',
        'FILTRABLE' => 'Y',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'UserGroup'], 'select' => ['ID']])['ID'],
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
        throw new RuntimeException('Can not create user group owner property. '.Phosagro\get_bitrix_error());
    }
};
