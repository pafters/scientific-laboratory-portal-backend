<?php

use Phosagro\Enum\PageType;

if (!defined('B_PROLOG_INCLUDED') || (B_PROLOG_INCLUDED !== true)) {
    exit;
}

echo PageType::EVENTS->render();
