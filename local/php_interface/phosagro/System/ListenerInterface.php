<?php

declare(strict_types=1);

namespace Phosagro\System;

use Bitrix\Main\EventManager;

interface ListenerInterface
{
    public function registerListeners(EventManager $eventManager): void;
}
