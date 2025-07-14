<?php

/**
 * Скопировно из /bitrix/components/bitrix/news/templates/.default
 * Отформатировано.
 * Удалено то что не нужно.
 * Оставлено то что непонятно зачем.
 *
 * @var array $arParams
 * @var array $arResult
 */
if (!defined('B_PROLOG_INCLUDED') || (B_PROLOG_INCLUDED !== true)) {
    exit;
}

$arTemplateParameters = [
    'VARIANT' => [
        'DEFAULT' => 'FULL',
        'MULTIPLE' => 'N',
        'NAME' => GetMessage('PHOSAGRO_BITRIX_NEWS_TEMPLATE_VARIANT'),
        'TYPE' => 'LIST',
        'VALUES' => [
            'LINK' => GetMessage('PHOSAGRO_BITRIX_NEWS_TEMPLATE_VARIANT_LINK'),
            'TEXT' => GetMessage('PHOSAGRO_BITRIX_NEWS_TEMPLATE_VARIANT_TEXT'),
            'FULL' => GetMessage('PHOSAGRO_BITRIX_NEWS_TEMPLATE_VARIANT_FULL'),
        ],
    ],
];
