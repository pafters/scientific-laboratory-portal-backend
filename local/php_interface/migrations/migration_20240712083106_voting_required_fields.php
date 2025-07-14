<?php

declare(strict_types=1);

use Phosagro\Migration\IblockHelper;

return static function (IblockHelper $iblocks): void {
    $iblocks->updateIblock('voting', 'Voting', [
        'FIELDS' => [
            'ACTIVE_FROM' => ['IS_REQUIRED' => 'Y'],
            'ACTIVE_TO' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_ADD' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_DELETE' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_EDIT' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_ADD' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_DELETE' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_EDIT' => ['IS_REQUIRED' => 'Y'],
        ],
    ]);
};
