<?php

declare(strict_types=1);

use Phosagro\Migration\IblockElementHelper;
use Phosagro\Migration\IblockHelper;

return static function (
    IblockElementHelper $elements,
    IblockHelper $iblocks,
): void {
    $iblocks->updateIblock('directory', 'UserGroup', [
        'FIELDS' => [
            'CODE' => ['DEFAULT_VALUE' => ['UNIQUE' => 'Y']],
        ],
    ]);

    $elements->createElement('directory', 'UserGroup', [
        'ACTIVE' => 'Y',
        'CODE' => 'schoolchildren',
        'NAME' => 'Школьники',
        'PROPERTY_VALUES' => ['OWNER' => '1'],
        'SORT' => 10,
    ]);

    $elements->createElement('directory', 'UserGroup', [
        'ACTIVE' => 'Y',
        'CODE' => 'students',
        'NAME' => 'Студенты',
        'PROPERTY_VALUES' => ['OWNER' => '1'],
        'SORT' => 20,
    ]);

    $elements->createElement('directory', 'UserGroup', [
        'ACTIVE' => 'Y',
        'CODE' => 'employees',
        'NAME' => 'Сотрудники',
        'PROPERTY_VALUES' => ['OWNER' => '1'],
        'SORT' => 30,
    ]);
};
