<?php

use Phosagro\Menu\MenuBuilder;
use Phosagro\ServiceContainer;
use Phosagro\Util\Json;

if (!defined('B_PROLOG_INCLUDED') || (B_PROLOG_INCLUDED !== true)) {
    exit;
}

echo Json::encode(['data' => ServiceContainer::getInstance()->get(MenuBuilder::class)->createFromBitrixData($arResult)->toApi()]);
