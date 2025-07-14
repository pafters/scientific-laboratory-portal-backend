<?php

declare(strict_types=1);

namespace Phosagro\Museum;

use Phosagro\Enum\LogEvent;
use Phosagro\Log\Logger;
use Phosagro\System\AgentInterface;

final class ScoreAccruerAgent implements AgentInterface
{
    public function __construct(
        private readonly ScoreAccruer $scoreAccruer,
        private readonly Logger $logger,
    ) {}

    public function execute(): void
    {
        try {
            $this->scoreAccruer->accrueScore();
        } catch (\Throwable $error) {
            $description = sprintf('[%d] %s', $error->getCode(), $error->getMessage());
            $this->logger->log(LogEvent::MUSEUM_SCORE_ACCRUAL_FAILED, '', $description);
        }
    }
}
