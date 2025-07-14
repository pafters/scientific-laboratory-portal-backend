<?php

declare(strict_types=1);

namespace Phosagro\Sms;

use Bitrix\Main\EventManager;
use Phosagro\System\ListenerInterface;

final class TestSmsServiceListener implements ListenerInterface
{
    public function registerListeners(EventManager $eventManager): void
    {
        $eventManager->addEventHandler('messageservice', 'onGetSmsSenders', static fn (): array => [
            new TestSmsService(),
        ]);
    }
}
