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

$item = $arResult;

$thumb = (string) CFile::GetPath($item['~PREVIEW_PICTURE'] ?? $item['~DETAIL_PICTURE']);
$thumb = ('' === $thumb) ? '' : $urlManager->makeAbsolute($thumb);

$picture = (string) CFile::GetPath($item['~DETAIL_PICTURE']);
$picture = ($picture === '') ? '' : $urlManager->makeAbsolute($picture);

$itemData = [
    'name' => (string) $item['~NAME'],
    'picture' => $picture,
    'preview' => (string) $item['PREVIEW_TEXT'],
    'since' => (string) $item['~ACTIVE_FROM'],
    'text' => (string) $item['DETAIL_TEXT'],
    'thumb' => $thumb,
    'url' => (string) $item['~DETAIL_PAGE_URL'],
];

$trimmedData = [];

foreach ($itemData as $key => $value) {
    $value = trim($value);
    if ('' !== $value) {
        $trimmedData[$key] = $value;
    }
}

echo PageType::DETAIL->render(['item' => $trimmedData]);
