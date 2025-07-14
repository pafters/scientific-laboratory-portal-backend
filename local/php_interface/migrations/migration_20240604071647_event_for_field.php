<?php

declare(strict_types=1);

use Phosagro\Migration\IblockPropertyHelper;

return static function (IblockPropertyHelper $proerpties): void {
    $proerpties->deleteProperty('content', 'Event', 'USER_GROUP');

    $proerpties->createPropertyString('content', 'Event', 'FOR', 'Для кого');
};
