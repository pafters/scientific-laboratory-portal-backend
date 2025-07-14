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

$APPLICATION->IncludeComponent(
    'bitrix:news.detail',
    'phosagro',
    [
        'ACTIVE_DATE_FORMAT' => $arParams['DETAIL_ACTIVE_DATE_FORMAT'],
        'ADD_ELEMENT_CHAIN' => $arParams['ADD_ELEMENT_CHAIN'],
        'ADD_SECTIONS_CHAIN' => $arParams['ADD_SECTIONS_CHAIN'],
        'BROWSER_TITLE' => $arParams['BROWSER_TITLE'],
        'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
        'CACHE_TIME' => $arParams['CACHE_TIME'],
        'CACHE_TYPE' => $arParams['CACHE_TYPE'],
        'CHECK_DATES' => $arParams['CHECK_DATES'],
        'DETAIL_URL' => $arResult['FOLDER'].$arResult['URL_TEMPLATES']['detail'],
        'DISPLAY_BOTTOM_PAGER' => $arParams['DETAIL_DISPLAY_BOTTOM_PAGER'],
        'DISPLAY_DATE' => $arParams['DISPLAY_DATE'],
        'DISPLAY_NAME' => $arParams['DISPLAY_NAME'],
        'DISPLAY_PICTURE' => $arParams['DISPLAY_PICTURE'],
        'DISPLAY_PREVIEW_TEXT' => $arParams['DISPLAY_PREVIEW_TEXT'],
        'DISPLAY_TOP_PAGER' => $arParams['DETAIL_DISPLAY_TOP_PAGER'],
        'ELEMENT_CODE' => $arResult['VARIABLES']['ELEMENT_CODE'],
        'ELEMENT_ID' => $arResult['VARIABLES']['ELEMENT_ID'],
        'FIELD_CODE' => $arParams['DETAIL_FIELD_CODE'],
        'FILE_404' => $arParams['FILE_404'],
        'GROUP_PERMISSIONS' => $arParams['GROUP_PERMISSIONS'],
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
        'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
        'IBLOCK_URL' => $arResult['FOLDER'].$arResult['URL_TEMPLATES']['news'],
        'INCLUDE_IBLOCK_INTO_CHAIN' => $arParams['INCLUDE_IBLOCK_INTO_CHAIN'],
        'MESSAGE_404' => $arParams['MESSAGE_404'],
        'META_DESCRIPTION' => $arParams['META_DESCRIPTION'],
        'META_KEYWORDS' => $arParams['META_KEYWORDS'],
        'PAGER_SHOW_ALL' => $arParams['DETAIL_PAGER_SHOW_ALL'],
        'PAGER_SHOW_ALWAYS' => 'N',
        'PAGER_TEMPLATE' => $arParams['DETAIL_PAGER_TEMPLATE'],
        'PAGER_TITLE' => $arParams['DETAIL_PAGER_TITLE'],
        'PROPERTY_CODE' => $arParams['DETAIL_PROPERTY_CODE'],
        'SECTION_CODE' => $arResult['VARIABLES']['SECTION_CODE'],
        'SECTION_ID' => $arResult['VARIABLES']['SECTION_ID'],
        'SECTION_URL' => $arResult['FOLDER'].$arResult['URL_TEMPLATES']['section'],
        'SET_CANONICAL_URL' => $arParams['DETAIL_SET_CANONICAL_URL'],
        'SET_LAST_MODIFIED' => $arParams['SET_LAST_MODIFIED'],
        'SET_STATUS_404' => $arParams['SET_STATUS_404'],
        'SET_TITLE' => $arParams['SET_TITLE'],
        'SHARE_HANDLERS' => $arParams['SHARE_HANDLERS'],
        'SHARE_HIDE' => $arParams['SHARE_HIDE'],
        'SHARE_SHORTEN_URL_KEY' => $arParams['SHARE_SHORTEN_URL_KEY'],
        'SHARE_SHORTEN_URL_LOGIN' => $arParams['SHARE_SHORTEN_URL_LOGIN'],
        'SHARE_TEMPLATE' => $arParams['SHARE_TEMPLATE'],
        'SHOW_404' => $arParams['SHOW_404'],
        'STRICT_SECTION_CHECK' => $arParams['STRICT_SECTION_CHECK'],
        'USE_PERMISSIONS' => $arParams['USE_PERMISSIONS'],
        'USE_SHARE' => $arParams['USE_SHARE'],
    ],
    false,
    ['HIDE_ICONS' => 'Y'],
);
