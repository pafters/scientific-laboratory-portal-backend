<?php

declare(strict_types=1);

use function Phosagro\get_bitrix_error;

return static function (): void {
    $error = [
        'en' => 'Not filled.',
        'ru' => 'Не заолнено.',
    ];

    $help = [
        'en' => 'Automatically filled.',
        'ru' => 'Заполняется автоматически.',
    ];

    $label = [
        'en' => 'Email confirmation request time',
        'ru' => 'Время отправки запроса подтверждения email',
    ];

    $settings = [
        'DEFAULT_VALUE' => ['DEFAULT_VALUE' => ['TYPE' => 'NONE', 'VALUE' => '']],
        'USE_SECOND' => 'Y',
        'USE_TIMEZONE' => 'N',
    ];

    $fieldManager = new CUserTypeEntity();
    $fieldResult = $fieldManager->Add([
        'EDIT_FORM_LABEL' => $label,
        'EDIT_IN_LIST' => 'N',
        'ENTITY_ID' => 'USER',
        'ERROR_MESSAGE' => $error,
        'FIELD_NAME' => 'UF_EMAIL_CONFIRM_REQ',
        'HELP_MESSAGE' => $help,
        'IS_SEARCHABLE' => 'N',
        'LIST_COLUMN_LABEL' => $label,
        'LIST_FILTER_LABEL' => $label,
        'MANDATORY' => 'N',
        'MULTIPLE' => 'N',
        'SETTINGS' => $settings,
        'SHOW_FILTER' => 'S',
        'SHOW_IN_LIST' => 'Y',
        'SORT' => 90,
        'USER_TYPE_ID' => 'datetime',
        'XML_ID' => 'UF_EMAIL_CONFIRM_REQ',
    ]);

    if (!$fieldResult) {
        throw new RuntimeException('Can not add user field. '.get_bitrix_error());
    }
};
