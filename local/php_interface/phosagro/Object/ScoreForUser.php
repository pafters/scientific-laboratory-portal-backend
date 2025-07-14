<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class ScoreForUser
{
    public function __construct(
        public readonly int $month,
        public readonly int $total,
        public readonly int $week,
    ) {}

    public function toApi(): array
    {
        return (array) $this;
    }
}
