<?php

use Phosagro\System\Api\Headers;

if (!defined('B_PROLOG_INCLUDED') || (B_PROLOG_INCLUDED !== true)) {
    exit;
}

if (filter_input(INPUT_GET, 'menu', FILTER_VALIDATE_BOOL)) {
    ob_clean();
    Headers::writeHeaders();
    $APPLICATION->IncludeComponent(
        'bitrix:menu',
        '',
        [
            'ALLOW_MULTI_SELECT' => 'N',
            'CHILD_MENU_TYPE' => 'left',
            'DELAY' => 'N',
            'MAX_LEVEL' => '4',
            'MENU_CACHE_GET_VARS' => [''],
            'MENU_CACHE_TIME' => '3600',
            'MENU_CACHE_TYPE' => 'N',
            'MENU_CACHE_USE_GROUPS' => 'Y',
            'ROOT_MENU_TYPE' => 'top',
            'USE_EXT' => 'N',
        ]
    );
}
