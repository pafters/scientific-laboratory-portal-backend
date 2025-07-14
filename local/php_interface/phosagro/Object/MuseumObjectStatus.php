<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class MuseumObjectStatus
{
    public function __construct(
        public readonly int $museumObjectStatusBonus,
        public readonly string $museumObjectStatusCode,
    ) {}
}
