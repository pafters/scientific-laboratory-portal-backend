<?php

declare(strict_types=1);

use Phosagro\Migration\IblockPropertyHelper;

return static function (IblockPropertyHelper $properties): void {
    $properties->createPropertyBool('event', 'Participant', 'REFUSED', 'Отказался от участия');
};
