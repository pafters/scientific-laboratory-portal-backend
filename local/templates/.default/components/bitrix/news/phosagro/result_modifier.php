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

$arParams['USE_SHARE'] = (string) ($arParams['USE_SHARE'] ?? 'N');
$arParams['USE_SHARE'] = 'Y' === $arParams['USE_SHARE'] ? 'Y' : 'N';
$arParams['SHARE_HIDE'] = (string) ($arParams['SHARE_HIDE'] ?? 'N');
$arParams['SHARE_HIDE'] = 'Y' === $arParams['SHARE_HIDE'] ? 'Y' : 'N';
$arParams['SHARE_TEMPLATE'] = (string) ($arParams['SHARE_TEMPLATE'] ?? 'N');
$arParams['SHARE_HANDLERS'] ??= [];
$arParams['SHARE_HANDLERS'] = is_array($arParams['SHARE_HANDLERS']) ? $arParams['SHARE_HANDLERS'] : [];
$arParams['SHARE_SHORTEN_URL_LOGIN'] = (string) ($arParams['SHARE_SHORTEN_URL_LOGIN'] ?? 'N');
$arParams['SHARE_SHORTEN_URL_KEY'] = (string) ($arParams['SHARE_SHORTEN_URL_KEY'] ?? 'N');
