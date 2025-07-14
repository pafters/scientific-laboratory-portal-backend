<?php

declare(strict_types=1);

namespace Phosagro\Rating;

use Phosagro\System\AgentInterface;

final class RatingCleanerAgent implements AgentInterface
{
    public function __construct(
        private readonly RatingCleaner $ratingCleaner,
    ) {}

    public function execute(): void
    {
        $this->ratingCleaner->cleanupRating();
    }
}
