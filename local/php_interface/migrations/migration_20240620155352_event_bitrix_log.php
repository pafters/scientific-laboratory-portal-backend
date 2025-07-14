<?php

declare(strict_types=1);

use Phosagro\Migration\IblockHelper;

return static function (IblockHelper $iblocks): void {
    $iblocks->updateIblock('content', 'Event', [
        'FIELDS' => [
            'ACTIVE_FROM' => ['IS_REQUIRED' => 'N'],
            'CODE' => ['DEFAULT_VALUE' => ['TRANSLITERATION' => 'Y', 'UNIQUE' => 'Y'], 'IS_REQUIRED' => 'Y'],
            'DETAIL_PICTURE' => ['IS_REQUIRED' => 'Y'],
            'DETAIL_TEXT' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_ADD' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_DELETE' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_EDIT' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_ADD' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_DELETE' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_EDIT' => ['IS_REQUIRED' => 'Y'],
            'PREVIEW_TEXT' => ['IS_REQUIRED' => 'N'],
            'SECTION_CODE' => ['DEFAULT_VALUE' => ['TRANSLITERATION' => 'Y', 'UNIQUE' => 'Y'], 'IS_REQUIRED' => 'Y'],
        ],
    ]);

    $codeValue = [
        'TRANSLITERATION' => 'Y',
        'TRANS_CASE' => 'L',
        'TRANS_EAT' => 'Y',
        'TRANS_LEN' => '100',
        'TRANS_OTHER' => '-',
        'TRANS_SPACE' => '-',
        'UNIQUE' => 'Y',
        'USE_GOOGLE' => 'N',
    ];

    $iblocks->updateIblock('event', 'Completion', [
        'FIELDS' => [
            'ACTIVE' => ['DEFAULT_VALUE' => 'N'],
            'CODE' => ['DEFAULT_VALUE' => ['UNIQUE' => 'Y'], 'IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_ADD' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_DELETE' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_EDIT' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_ADD' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_DELETE' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_EDIT' => ['IS_REQUIRED' => 'Y'],
            'NAME' => ['DEFAULT_VALUE' => '(заполняется автоматически)'],
        ],
    ]);

    $iblocks->updateIblock('event', 'Participant', [
        'FIELDS' => [
            'ACTIVE' => ['DEFAULT_VALUE' => 'N'],
            'CODE' => ['DEFAULT_VALUE' => $codeValue, 'IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_ADD' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_DELETE' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_EDIT' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_ADD' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_DELETE' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_EDIT' => ['IS_REQUIRED' => 'Y'],
            'NAME' => ['DEFAULT_VALUE' => '(заполняется автоматически)'],
        ],
    ]);

    $iblocks->updateIblock('event', 'Task', [
        'FIELDS' => [
            'LOG_ELEMENT_ADD' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_DELETE' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_EDIT' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_ADD' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_DELETE' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_EDIT' => ['IS_REQUIRED' => 'Y'],
        ],
    ]);

    $iblocks->updateIblock('event', 'Template', [
        'FIELDS' => [
            'LOG_ELEMENT_ADD' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_DELETE' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_EDIT' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_ADD' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_DELETE' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_EDIT' => ['IS_REQUIRED' => 'Y'],
        ],
    ]);
};
