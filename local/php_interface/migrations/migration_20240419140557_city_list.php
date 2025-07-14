<?php

declare(strict_types=1);

use Bitrix\Iblock\IblockTable;

return static function (): void {
    $manager = new CIBlockElement();

    $result = $manager->Add([
        'CODE' => 'balakovo',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'City'], 'select' => ['ID']])['ID'],
        'NAME' => 'Балаково',
        'PROPERTY_VALUES' => ['OWNER' => 1],
        'SORT' => 10,
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create balakovo city. '.$manager->LAST_ERROR);
    }

    $result = $manager->Add([
        'CODE' => 'volkhov',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'City'], 'select' => ['ID']])['ID'],
        'NAME' => 'Волхов',
        'PROPERTY_VALUES' => ['OWNER' => 1],
        'SORT' => 20,
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create volkhov city. '.$manager->LAST_ERROR);
    }

    $result = $manager->Add([
        'CODE' => 'kirovsk',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'City'], 'select' => ['ID']])['ID'],
        'NAME' => 'Кировск',
        'PROPERTY_VALUES' => ['OWNER' => 1],
        'SORT' => 30,
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create kirovsk city. '.$manager->LAST_ERROR);
    }

    $result = $manager->Add([
        'CODE' => 'cherepovets',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'City'], 'select' => ['ID']])['ID'],
        'NAME' => 'Череповец',
        'PROPERTY_VALUES' => ['OWNER' => 1],
        'SORT' => 40,
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create cherepovets city. '.$manager->LAST_ERROR);
    }
};
