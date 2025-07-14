<?php

declare(strict_types=1);

use Phosagro\Iblocks;

return static function (): void {
    $error = [
        'en' => 'City is not selected.',
        'ru' => 'Не выбран город.',
    ];

    $help = [
        'en' => 'Selected by the user during registration.',
        'ru' => 'Выбирается пользователем при регистрации.',
    ];

    $label = [
        'en' => 'City',
        'ru' => 'Город',
    ];

    $settings = [
        'ACTIVE_FILTER' => 'N',
        'DEFAULT_VALUE' => '',
        'DISPLAY' => 'LIST',
        'IBLOCK_ID' => Iblocks::cityId(),
        'IBLOCK_TYPE_ID' => 'directory',
        'LIST_HEIGHT' => '1',
    ];

    $fieldManager = new CUserTypeEntity();
    $fieldResult = $fieldManager->Add([
        'EDIT_FORM_LABEL' => $label,
        'EDIT_IN_LIST' => 'Y',
        'ENTITY_ID' => 'USER',
        'ERROR_MESSAGE' => $error,
        'FIELD_NAME' => 'UF_CITY',
        'HELP_MESSAGE' => $help,
        'IS_SEARCHABLE' => 'N',
        'LIST_COLUMN_LABEL' => $label,
        'LIST_FILTER_LABEL' => $label,
        'MANDATORY' => 'Y',
        'MULTIPLE' => 'N',
        'SETTINGS' => $settings,
        'SHOW_FILTER' => 'S',
        'SHOW_IN_LIST' => 'Y',
        'SORT' => 10,
        'USER_TYPE_ID' => 'iblock_element',
        'XML_ID' => 'UF_CITY',
    ]);

    if (!$fieldResult) {
        throw new RuntimeException('Can not add user field. '.Phosagro\get_bitrix_error());
    }
};
