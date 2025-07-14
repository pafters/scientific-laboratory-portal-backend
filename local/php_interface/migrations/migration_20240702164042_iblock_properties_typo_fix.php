<?php

declare(strict_types=1);

use Phosagro\Migration\IblockPropertyHelper;

return static function (IblockPropertyHelper $properties): void {
    $properties->updateProperty(
        'content',
        'Event',
        'PREVENT_REGISTRATION_CLOSE_EMAIL',
        [
            'NAME' => 'Прекратить отправку писем о завершении регистрации',
        ],
    );

    $properties->updateProperty(
        'event',
        'Participant',
        'PREVENT_CONFIRM_EMAIL',
        [
            'NAME' => 'Прекратить отправку писем о подтверждении заявки',
        ],
    );

    $properties->updateProperty(
        'event',
        'Participant',
        'PREVENT_REJECT_EMAIL',
        [
            'NAME' => 'Прекратить отправку писем об отклонении заявки',
        ],
    );
};
