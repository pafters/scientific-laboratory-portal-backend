<?php

declare(strict_types=1);

namespace Phosagro\Museum;

use Phosagro\Enum\LogEvent;
use Phosagro\Log\Logger;
use Phosagro\System\AgentInterface;

final class FileCleanerAgent implements AgentInterface
{
    public function __construct(
        private readonly FileCleaner $fileCleaner,
        private readonly Logger $logger,
    ) {}

    public function execute(): void
    {
        try {
            $this->fileCleaner->cleanFiles();
        } catch (\Throwable $error) {
            $description = sprintf('[%d] %s', $error->getCode(), $error->getMessage());
            $this->logger->log(LogEvent::MUSEUM_FILE_CLEANING_FAILED, '', $description);
        }
    }
}
