<?php

declare(strict_types=1);

return static function (): void {
    $enumManager = new CUserFieldEnum();
    $fieldManager = new CUserTypeEntity();

    $error = [
        'en' => 'Educational institution is not selected.',
        'ru' => 'Не выбрано учебное заведение.',
    ];

    $help = [
        'en' => 'Selected by the user during registration.',
        'ru' => 'Выбирается пользователем при регистрации.',
    ];

    $label = [
        'en' => 'Educational institution',
        'ru' => 'Учебное заведение',
    ];

    $settings = [
        'CAPTION_NO_VALUE' => '(не выбрано)',
        'DISPLAY' => 'LIST',
        'LIST_HEIGHT' => '1',
        'SHOW_NO_VALUE' => 'N',
    ];

    $fieldResult = $fieldManager->Add([
        'EDIT_FORM_LABEL' => $label,
        'EDIT_IN_LIST' => 'Y',
        'ENTITY_ID' => 'USER',
        'ERROR_MESSAGE' => $error,
        'FIELD_NAME' => 'UF_EDU_TYPE',
        'HELP_MESSAGE' => $help,
        'IS_SEARCHABLE' => 'N',
        'LIST_COLUMN_LABEL' => $label,
        'LIST_FILTER_LABEL' => $label,
        'MANDATORY' => 'N',
        'MULTIPLE' => 'N',
        'SETTINGS' => $settings,
        'SHOW_FILTER' => 'S',
        'SHOW_IN_LIST' => 'Y',
        'SORT' => 20,
        'USER_TYPE_ID' => 'enumeration',
        'XML_ID' => 'UF_EDU_TYPE',
    ]);

    if (!$fieldResult) {
        throw new RuntimeException('Can not add user field. '.Phosagro\get_bitrix_error());
    }

    $enumResult = $enumManager->SetEnumValues($fieldResult, [
        'n1' => [
            'DEF' => 'N',
            'SORT' => 10,
            'VALUE' => 'школа',
            'XML_ID' => 'SCHOOL',
        ],
        'n2' => [
            'DEF' => 'N',
            'SORT' => 20,
            'VALUE' => 'суз',
            'XML_ID' => 'SUZ',
        ],
        'n3' => [
            'DEF' => 'N',
            'SORT' => 30,
            'VALUE' => 'вуз',
            'XML_ID' => 'VUZ',
        ],
    ]);

    if (!$enumResult) {
        throw new RuntimeException('Can not add user field enum. '.Phosagro\get_bitrix_error());
    }

    $error = [
        'en' => 'Educational institution name is not specified.',
        'ru' => 'Не указано наименование учебного заведения.',
    ];

    $help = [
        'en' => 'Filled by the user during registration.',
        'ru' => 'Заполняется пользователем при регистрации.',
    ];

    $label = [
        'en' => 'Educational institution name',
        'ru' => 'Наименование учебного заведения',
    ];

    $settings = [
        'DEFAULT_VALUE' => '',
        'MAX_LENGTH' => '0',
        'MIN_LENGTH' => '0',
        'REGEXP' => '',
        'ROWS' => '1',
        'SIZE' => '50',
    ];

    $fieldResult = $fieldManager->Add([
        'EDIT_FORM_LABEL' => $label,
        'EDIT_IN_LIST' => 'Y',
        'ENTITY_ID' => 'USER',
        'ERROR_MESSAGE' => $error,
        'FIELD_NAME' => 'UF_EDU_NAME',
        'HELP_MESSAGE' => $help,
        'IS_SEARCHABLE' => 'N',
        'LIST_COLUMN_LABEL' => $label,
        'LIST_FILTER_LABEL' => $label,
        'MANDATORY' => 'N',
        'MULTIPLE' => 'N',
        'SETTINGS' => $settings,
        'SHOW_FILTER' => 'S',
        'SHOW_IN_LIST' => 'Y',
        'SORT' => 30,
        'USER_TYPE_ID' => 'string',
        'XML_ID' => 'UF_EDU_NAME',
    ]);

    if (!$fieldResult) {
        throw new RuntimeException('Can not add user field. '.Phosagro\get_bitrix_error());
    }
};
