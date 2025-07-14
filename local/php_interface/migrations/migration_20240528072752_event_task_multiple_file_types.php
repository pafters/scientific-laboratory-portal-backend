<?php

declare(strict_types=1);

use Phosagro\Migration\IblockPropertyHelper;

return static function (IblockPropertyHelper $properties): void {
    $properties->updateProperty('event', 'Task', 'FILE_TYPES', [
        'MULTIPLE' => 'Y',
        'MULTIPLE_CNT' => 1,
    ]);
};
