<?php

declare(strict_types=1);

use Phosagro\Migration\IblockPropertyHelper;

return static function (IblockPropertyHelper $properties): void {
    $properties->updateProperty('event', 'Completion', 'FILES', [
        'WITH_DESCRIPTION' => 'Y',
    ]);
};
