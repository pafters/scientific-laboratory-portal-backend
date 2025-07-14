<?php

declare(strict_types=1);

namespace Phosagro\Rating;

final class RatingRow
{
    public function __construct(
        public readonly RatingItem $item,
        public readonly int $place,
    ) {}
}
