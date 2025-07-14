<?php

declare(strict_types=1);

use Phosagro\Iblocks;

return static function (): void {
    $error = [
        'en' => 'Phosagro company is not selected.',
        'ru' => 'Не выбрана компания Фосагро.',
    ];

    $help = [
        'en' => 'Selected by the user during registration.',
        'ru' => 'Выбирается пользователем при регистрации.',
    ];

    $label = [
        'en' => 'Phosagro company',
        'ru' => 'Компания Фосагро',
    ];

    $settings = [
        'ACTIVE_FILTER' => 'N',
        'DEFAULT_VALUE' => '',
        'DISPLAY' => 'LIST',
        'IBLOCK_ID' => Iblocks::phosagroCompanyId(),
        'IBLOCK_TYPE_ID' => 'directory',
        'LIST_HEIGHT' => '1',
    ];

    $fieldManager = new CUserTypeEntity();
    $fieldResult = $fieldManager->Add([
        'EDIT_FORM_LABEL' => $label,
        'EDIT_IN_LIST' => 'Y',
        'ENTITY_ID' => 'USER',
        'ERROR_MESSAGE' => $error,
        'FIELD_NAME' => 'UF_PHOSAGRO_COMPANY',
        'HELP_MESSAGE' => $help,
        'IS_SEARCHABLE' => 'N',
        'LIST_COLUMN_LABEL' => $label,
        'LIST_FILTER_LABEL' => $label,
        'MANDATORY' => 'N',
        'MULTIPLE' => 'N',
        'SETTINGS' => $settings,
        'SHOW_FILTER' => 'S',
        'SHOW_IN_LIST' => 'Y',
        'SORT' => 40,
        'USER_TYPE_ID' => 'iblock_element',
        'XML_ID' => 'UF_PHOSAGRO_COMPANY',
    ]);

    if (!$fieldResult) {
        throw new RuntimeException('Can not add user field. '.Phosagro\get_bitrix_error());
    }
};
