<?php

declare(strict_types=1);

use Phosagro\Migration\IblockPropertyHelper;

return static function (IblockPropertyHelper $propertyHelper): void {
    $propertyHelper->createPropertyElement(
        'content',
        'Event',
        'DISPLAY_GROUPS',
        'Показывать только выбранным группам',
        'directory',
        'UserGroup',
        false,
        [
            'MULTIPLE' => 'Y',
            'MULTIPLE_CNT' => 1,
        ],
    );

    $propertyHelper->createPropertyElement(
        'content',
        'Event',
        'PARTICIPANT_GROUPS',
        'Разрешить участие только выбранным группам',
        'directory',
        'UserGroup',
        false,
        [
            'MULTIPLE' => 'Y',
            'MULTIPLE_CNT' => 1,
        ],
    );
};
