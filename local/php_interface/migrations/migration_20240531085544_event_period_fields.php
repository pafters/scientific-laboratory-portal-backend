<?php

declare(strict_types=1);

use Phosagro\Migration\IblockHelper;
use Phosagro\Migration\IblockPropertyHelper;

return static function (IblockHelper $iblocks, IblockPropertyHelper $properties): void {
    $iblocks->updateIblock('content', 'Event', [
        'FIELDS' => [
            'ACTIVE_FROM' => [
                'IS_REQUIRED' => 'N',
            ],
            'CODE' => [
                'DEFAULT_VALUE' => [
                    'TRANSLITERATION' => 'Y',
                    'UNIQUE' => 'Y',
                ],
                'IS_REQUIRED' => 'Y',
            ],
            'DETAIL_PICTURE' => [
                'IS_REQUIRED' => 'Y',
            ],
            'DETAIL_TEXT' => [
                'IS_REQUIRED' => 'Y',
            ],
            'PREVIEW_TEXT' => [
                'IS_REQUIRED' => 'N',
            ],
            'SECTION_CODE' => [
                'DEFAULT_VALUE' => [
                    'TRANSLITERATION' => 'Y',
                    'UNIQUE' => 'Y',
                ],
                'IS_REQUIRED' => 'Y',
            ],
        ],
    ]);

    $properties->createPropertyDate('content', 'Event', 'STARTS_AT', 'Начало провердения', true);

    $properties->createPropertyDate('content', 'Event', 'ENDS_AT', 'Окончание провердения');
};
