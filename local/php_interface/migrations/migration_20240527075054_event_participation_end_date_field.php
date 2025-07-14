<?php

declare(strict_types=1);

use Phosagro\Migration\IblockPropertyHelper;

return static function (IblockPropertyHelper $properties): void {
    $properties->createPropertyDate('content', 'Event', 'PARTICIPATION_ENDS_AT', 'Дата завершения регистации');
};
