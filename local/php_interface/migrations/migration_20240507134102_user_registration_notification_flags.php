<?php

declare(strict_types=1);

$addField = static function (
    string $code,
    string $nameEn,
    string $nameRu,
    int $sort,
): void {
    $error = [
        'en' => 'Not selected.',
        'ru' => 'Не выбрано.',
    ];

    $help = [
        'en' => 'Automatically marked.',
        'ru' => 'Отмечается автоматически.',
    ];

    $label = [
        'en' => $nameEn,
        'ru' => $nameRu,
    ];

    $settings = [
        'DEFAULT_VALUE' => '0',
        'DISPLAY' => 'CHECKBOX',
        'LABEL' => ['нет', 'да'],
        'LABEL_CHECKBOX' => 'да',
    ];

    $fieldManager = new CUserTypeEntity();
    $fieldResult = $fieldManager->Add([
        'EDIT_FORM_LABEL' => $label,
        'EDIT_IN_LIST' => 'N',
        'ENTITY_ID' => 'USER',
        'ERROR_MESSAGE' => $error,
        'FIELD_NAME' => $code,
        'HELP_MESSAGE' => $help,
        'IS_SEARCHABLE' => 'N',
        'LIST_COLUMN_LABEL' => $label,
        'LIST_FILTER_LABEL' => $label,
        'MANDATORY' => 'N',
        'MULTIPLE' => 'N',
        'SETTINGS' => $settings,
        'SHOW_FILTER' => 'I',
        'SHOW_IN_LIST' => 'Y',
        'SORT' => $sort,
        'USER_TYPE_ID' => 'boolean',
        'XML_ID' => $code,
    ]);

    if (!$fieldResult) {
        throw new RuntimeException('Can not add user field. '.Phosagro\get_bitrix_error());
    }
};

return static function () use ($addField): void {
    $addField(
        'UF_STOP_REG_ADM_NOTIFY',
        'Stop sending moderator notifications about registration',
        'Прекратить отправку уведомлений модератора о регистрации',
        60,
    );

    $addField(
        'UF_STOP_REG_USER_CONFIRM',
        'Stop sending user notifications about registration confirmation',
        'Прекратить отправку уведомлений пользователя о подтверждении регистрации',
        70,
    );
    
    $addField(
        'UF_STOP_REG_USER_REJECT',
        'Stop sending user notifications about registration rejection',
        'Прекратить отправку уведомлений пользователя об отклонении регистрации',
        80,
    );
};
