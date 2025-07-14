<?php

declare(strict_types=1);

use Bitrix\Highloadblock\HighloadBlockLangTable;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Result;

return static function (): void {
    $checkResult = static function (Result $result): void {
        if (!$result->isSuccess()) {
            throw new RuntimeException(implode(' ', $result->getErrorMessages()));
        }
    };

    $getHlBlockId = static function (string $name): int {
        return (int) HighloadBlockTable::getRow(['filter' => ['=NAME' => $name], 'select' => ['ID']])['ID'];
    };

    $checkResult(
        HighloadBlockTable::add([
            'NAME' => 'Migrations',
            'TABLE_NAME' => 'picom_migrations',
        ]),
    );

    $checkResult(
        HighloadBlockLangTable::add([
            'ID' => $getHlBlockId('Migrations'),
            'LID' => 'en',
            'NAME' => 'Migrations',
        ]),
    );

    $checkResult(
        HighloadBlockLangTable::add([
            'ID' => $getHlBlockId('Migrations'),
            'LID' => 'ru',
            'NAME' => 'Миграции',
        ]),
    );

    $result = (new CUserTypeEntity())->Add([
        'EDIT_FORM_LABEL' => ['ru' => 'Имя миграции', 'en' => 'Migration name'],
        'EDIT_IN_LIST' => '',
        'ENTITY_ID' => "HLBLOCK_{$getHlBlockId('Migrations')}",
        'FIELD_NAME' => 'UF_NAME',
        'IS_SEARCHABLE' => 'N',
        'LIST_COLUMN_LABEL' => ['ru' => 'Имя миграции', 'en' => 'Migration name'],
        'LIST_FILTER_LABEL' => ['ru' => 'Имя миграции', 'en' => 'Migration name'],
        'MANDATORY' => 'Y',
        'MULTIPLE' => 'N',
        'SETTINGS' => ['SIZE' => '50', 'ROWS' => '1'],
        'SHOW_FILTER' => 'S',
        'SHOW_IN_LIST' => '',
        'SORT' => 10,
        'USER_TYPE_ID' => 'string',
        'XML_ID' => 'MIGRATION_NAME',
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create migrations highload-block. '.Phosagro\get_bitrix_error());
    }
};
