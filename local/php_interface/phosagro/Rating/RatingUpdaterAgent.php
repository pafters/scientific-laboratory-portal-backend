<?php

declare(strict_types=1);

namespace Phosagro\Rating;

use Phosagro\System\AgentInterface;

final class RatingUpdaterAgent implements AgentInterface
{
    public function __construct(
        private readonly RatingUpdater $ratingUpdater,
    ) {}

    public function execute(): void
    {
        $this->ratingUpdater->updateRating();
    }
}
