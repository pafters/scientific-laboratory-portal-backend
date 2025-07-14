<?php

declare(strict_types=1);

namespace Phosagro\Rating;

final class RatingTable
{
    /**
     * @param RatingRow[] $top
     */
    public function __construct(
        public readonly int $of = 0,
        public readonly int $place = 0,
        public readonly array $top = [],
    ) {}
}
