<?php

declare(strict_types=1);

use Bitrix\Iblock\IblockTable;

return static function (): void {
    // Создаем свойство "Название сайта" в контакты
    $manager = new CIBlockProperty();

    $result = $manager->Add([
        'ACTIVE' => 'Y',
        'CODE' => 'ADDRESS',
        'IBLOCK_ID' => IblockTable::getRow(['filter' => ['=API_CODE' => 'Contacts'], 'select' => ['ID']])['ID'],
        'IS_REQUIRED' => 'Y',
        'NAME' => 'Адрес',
        'PROPERTY_TYPE' => 'S',
        'MULTIPLE' => 'N',
        'SEARCHABLE' => 'N',
        'SORT' => 10,
        'WITH_DESCRIPTION' => 'N',
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create ADDRESS in the Contacts infoblock. ' . $manager->LAST_ERROR);
    }
};
