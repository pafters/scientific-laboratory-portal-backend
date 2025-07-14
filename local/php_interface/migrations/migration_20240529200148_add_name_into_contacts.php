<?php

declare(strict_types=1);

use Bitrix\Iblock\IblockTable;

return static function (): void {
    // Создаем свойство "Название сайта" в контакты
    $manager = new CIBlockProperty();

    $result = $manager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'SITE_NAME',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'Contacts'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'Y',
        'NAME' => 'Название сайта',
        'PROPERTY_TYPE' => 'S',
        'MULTIPLE' => 'N',
        'SEARCHABLE' => 'N',
        'SORT' => 50,
        'WITH_DESCRIPTION' => 'N',
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create SITE_NAME in the Contacts infoblock. ' . $manager->LAST_ERROR);
    }
};
