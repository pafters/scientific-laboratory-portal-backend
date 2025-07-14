<?php
use Phosagro\ApiResolver;
use Phosagro\ServiceContainer;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

ServiceContainer::getInstance()->get(ApiResolver::class)->resolve($_SERVER['REQUEST_URI'], []);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
