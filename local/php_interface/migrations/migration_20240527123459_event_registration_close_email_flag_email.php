<?php

declare(strict_types=1);

use Phosagro\Migration\IblockPropertyHelper;

return static function (IblockPropertyHelper $properties): void {
    $properties->createPropertyBool(
        'content',
        'Event',
        'PREVENT_REGISTRATION_CLOSE_EMAIL',
        'Перкратить отправку писем о завершении регистрации',
    );
};
