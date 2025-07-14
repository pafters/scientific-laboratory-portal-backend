<?php

declare(strict_types=1);

namespace Phosagro\Museum;

use Phosagro\Enum\LogEvent;
use Phosagro\Log\Logger;
use Phosagro\System\AgentInterface;

final class VisitsUpdaterAgent implements AgentInterface
{
    public function __construct(
        private readonly VisitsUpdater $visitsUpdater,
        private readonly Logger $logger,
    ) {}

    public function execute(): void
    {
        try {
            $this->visitsUpdater->updateVisits();
        } catch (MuseumException $error) {
            $this->logger->log(LogEvent::MUSEUM_DATABASE_WRONG, $error->item, $error->getMessage());
        }
    }
}
