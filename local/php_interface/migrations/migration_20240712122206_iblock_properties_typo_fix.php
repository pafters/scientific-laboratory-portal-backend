<?php

declare(strict_types=1);

use Phosagro\Migration\IblockPropertyHelper;

return static function (IblockPropertyHelper $properties): void {
    $properties->updateProperty('content', 'Event', 'STARTS_AT', ['NAME' => 'Начало проведения']);
    $properties->updateProperty('content', 'Event', 'ENDS_AT', ['NAME' => 'Окончание проведения']);
};
