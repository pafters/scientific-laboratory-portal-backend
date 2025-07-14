<?php

declare(strict_types=1);

namespace Phosagro\Log;

use Phosagro\Enum\LogEvent;

final class Logger
{
    public function log(LogEvent $event, string $item = '', string $description = ''): void
    {
        $result = \CEventLog::Log($event->getBitrixSeverity(), $event->name, '', $item, $description);

        if (!$result) {
            throw new \RuntimeException('Can not write log.');
        }
    }
}
