<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class TaskPlace
{
    public function __construct(
        public readonly float $placeLatitude,
        public readonly float $placeLongitude,
    ) {}
}
