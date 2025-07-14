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

use Phosagro\Enum\PageType;
use Phosagro\ServiceContainer;
use Phosagro\System\UrlManager;

$urlManager = ServiceContainer::getInstance()->get(UrlManager::class);
$variant = $arParams['VARIANT'] ?? 'FULL';

$itemList = [];

foreach ($arResult['ITEMS'] as $item) {
    $itemData = [
        'name' => (string) $item['~NAME'],
        'url' => (string) $item['~DETAIL_PAGE_URL'],
    ];

    if (('TEXT' === $variant) || ('FULL' === $variant)) {
        $itemData += [
            'preview' => (string) $item['~PREVIEW_TEXT'],
            'since' => (string) $item['~ACTIVE_FROM'],
        ];
    }

    if ('FULL' === $variant) {
        $thumb = (string) CFile::GetPath($item['~PREVIEW_PICTURE'] ?? $item['~DETAIL_PICTURE']);
        $thumb = ('' === $thumb) ? '' : $urlManager->makeAbsolute($thumb);
        $itemData += [
            'thumb' => $thumb,
        ];
    }

    $trimmedData = [];

    foreach ($itemData as $key => $value) {
        $value = trim($value);
        if ('' !== $value) {
            $trimmedData[$key] = $value;
        }
    }

    ksort($trimmedData, SORT_STRING);

    $itemList[] = $trimmedData;
}

echo PageType::LIST->render(['items' => $itemList]);
