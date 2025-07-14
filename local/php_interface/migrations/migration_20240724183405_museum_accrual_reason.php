<?php

declare(strict_types=1);

use Phosagro\Migration\IblockElementHelper;

return static function (IblockElementHelper $elements): void {
    $elements->createElement('directory', 'AccrualReason', [
        'CODE' => 'museum_visit',
        'NAME' => 'Посещён музей',
        'PROPERTY_VALUES' => ['OWNER' => '1'],
        'SORT' => 40,
    ]);
};
