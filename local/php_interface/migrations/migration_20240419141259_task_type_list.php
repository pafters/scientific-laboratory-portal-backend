<?php

declare(strict_types=1);

use Bitrix\Iblock\IblockTable;

return static function (): void {
    $manager = new CIBlockElement();

    $result = $manager->Add([
        'CODE' => 'fill_out_the_form',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'TaskType'], 'select' => ['ID']])['ID'],
        'NAME' => 'Заполни форму',
        'PROPERTY_VALUES' => ['OWNER' => 1],
        'SORT' => 10,
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create fill out the form task type. '.$manager->LAST_ERROR);
    }

    $result = $manager->Add([
        'CODE' => 'visit_the_place',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'TaskType'], 'select' => ['ID']])['ID'],
        'NAME' => 'Посетить место',
        'PROPERTY_VALUES' => ['OWNER' => 1],
        'SORT' => 20,
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create visit the place task type. '.$manager->LAST_ERROR);
    }

    $result = $manager->Add([
        'CODE' => 'upload_file',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'TaskType'], 'select' => ['ID']])['ID'],
        'NAME' => 'Загрузи файл',
        'PROPERTY_VALUES' => ['OWNER' => 1],
        'SORT' => 30,
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create upload file task type. '.$manager->LAST_ERROR);
    }

    $result = $manager->Add([
        'CODE' => 'watch_the_video',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'TaskType'], 'select' => ['ID']])['ID'],
        'NAME' => 'Посмотри видеоролик',
        'PROPERTY_VALUES' => ['OWNER' => 1],
        'SORT' => 40,
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create watch the video task type. '.$manager->LAST_ERROR);
    }
};
