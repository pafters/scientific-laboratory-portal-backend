<?php

declare(strict_types=1);

use Phosagro\Migration\IblockHelper;
use Phosagro\Migration\IblockPermissionHelper;

return static function (IblockHelper $iblocks, IblockPermissionHelper $permissions): void {
    $iblocks->createIblock(
        'content',
        'Faq',
        'Часто задаваемый вопрос',
        'Часто задаваемый вопрос',
        'Часто задаваемые вопросы',
        [
            'FIELDS' => [
                'DETAIL_TEXT' => ['IS_REQUIRED' => 'Y'],
                'LOG_ELEMENT_ADD' => ['IS_REQUIRED' => 'Y'],
                'LOG_ELEMENT_DELETE' => ['IS_REQUIRED' => 'Y'],
                'LOG_ELEMENT_EDIT' => ['IS_REQUIRED' => 'Y'],
                'LOG_SECTION_ADD' => ['IS_REQUIRED' => 'Y'],
                'LOG_SECTION_DELETE' => ['IS_REQUIRED' => 'Y'],
                'LOG_SECTION_EDIT' => ['IS_REQUIRED' => 'Y'],
            ],
        ],
    );

    $permissions->setIblockPermissons('content', 'Faq', [
        'education_moderator' => 'R',
        'event_moderator' => 'R',
        'guest' => 'R',
        'public_moderator' => 'W',
        'technical_administrator' => 'X',
    ]);
};
