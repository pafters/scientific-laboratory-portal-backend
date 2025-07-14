<?php

declare(strict_types=1);

namespace Phosagro\Rating;

final class RatingResult
{
    public function __construct(
        public readonly RatingTable $event,
        public readonly RatingTable $month,
        public readonly RatingTable $total,
        public readonly RatingTable $week,
    ) {}
}
