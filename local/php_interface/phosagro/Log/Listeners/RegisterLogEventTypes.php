<?php

declare(strict_types=1);

namespace Phosagro\Log\Listeners;

use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Phosagro\Enum\LogEvent;
use Phosagro\System\ListenerInterface;

final class RegisterLogEventTypes implements ListenerInterface
{
    public function registerListeners(EventManager $eventManager): void
    {
        $eventManager->addEventHandler('main', 'OnEventLogGetAuditTypes', $this->getEventTypes(...));
    }

    private function getEventTypes(): array
    {
        $result = [];

        foreach (LogEvent::cases() as $event) {
            $result[$event->name] = \sprintf('[%s] %s', $event->name, Loc::getMessage($event->name));
        }

        return $result;
    }
}
