<?php

declare(strict_types=1);

use Phosagro\Migration\IblockHelper;

return static function (IblockHelper $iblocks): void {
    $iblocks->updateIblock('event', 'Participant', [
        'FIELDS' => [
            'ACTIVE' => [
                'DEFAULT_VALUE' => 'N',
            ],
            'CODE' => [
                'DEFAULT_VALUE' => [
                    'TRANSLITERATION' => 'Y',
                    'TRANS_CASE' => 'L',
                    'TRANS_EAT' => 'Y',
                    'TRANS_LEN' => '100',
                    'TRANS_OTHER' => '-',
                    'TRANS_SPACE' => '-',
                    'UNIQUE' => 'Y',
                    'USE_GOOGLE' => 'N',
                ],
                'IS_REQUIRED' => 'Y',
            ],
            'NAME' => [
                'DEFAULT_VALUE' => '(заполняется автоматически)',
            ],
        ],
    ]);
};
