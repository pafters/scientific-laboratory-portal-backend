<?php

declare(strict_types=1);

use Phosagro\Iblocks;

return static function (): void {
    CIBlock::SetFields(
        Iblocks::eventId(),
        [
            'ACTIVE_FROM' => [
                'IS_REQUIRED' => 'Y',
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
    );
};
