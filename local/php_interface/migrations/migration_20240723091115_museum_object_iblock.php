<?php

declare(strict_types=1);

use Phosagro\Migration\IblockHelper;
use Phosagro\Migration\IblockPropertyHelper;

return static function (IblockHelper $iblocks, IblockPropertyHelper $properties): void {
    $iblocks->createIblock('directory', 'MuseumObject', 'Объект музея', 'Объект музея', 'Объекты музея', [
        'FIELDS' => [
            'CODE' => ['DEFAULT_VALUE' => ['UNIQUE' => 'Y'], 'IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_ADD' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_DELETE' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_EDIT' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_ADD' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_DELETE' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_EDIT' => ['IS_REQUIRED' => 'Y'],
        ],
    ]);

    $properties->createPropertyString('directory', 'MuseumObject', 'STATUS', 'Статусы прохождения', false, [
        'HINT' => 'Описание - число, количество баллов.',
        'MULTIPLE' => 'Y',
        'MULTIPLE_CNT' => 1,
        'WITH_DESCRIPTION' => 'Y',
    ]);
};
