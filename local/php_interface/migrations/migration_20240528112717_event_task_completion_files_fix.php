<?php

declare(strict_types=1);

use Phosagro\Migration\DatabaseHelper;
use Phosagro\Migration\IblockHelper;

return static function (DatabaseHelper $database, IblockHelper $iblocks): void {
    $found = CIBlockProperty::GetList(
        [
            'id' => 'asc',
        ],
        [
            'CODE' => 'ANSWER',
            'NAME' => 'Файлы для задания «загрузи файл»',
            'PROPERTY_ID' => $iblocks->getIblockId('event', 'Completion'),
        ],
    );

    $row = $found->Fetch();

    if (!$row) {
        throw new RuntimeException('Not found wrong ANSWER property.');
    }

    if ($found->Fetch()) {
        throw new RuntimeException('Found more than one wrong ANSWER property.');
    }

    $propertyId = (int) $row['ID'];

    $manager = new CIBlockProperty();

    $database->assertSuccess(
        $manager->Update($propertyId, [
            'CODE' => 'FILES',
        ]),
        'iblock property',
        "event/Completion/{$propertyId}",
        'update',
        $manager->LAST_ERROR
    );
};
