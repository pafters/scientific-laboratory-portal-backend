<?php

declare(strict_types=1);

use Phosagro\Migration\IblockPropertyHelper;

return static function (IblockPropertyHelper $properties): void {
    $properties->updateProperty('content', 'Event', 'PARTICIPATION_ENDS_AT', [
        'NAME' => 'Дата завершения регистрации',
    ]);
};
