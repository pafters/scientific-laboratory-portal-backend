<?php

declare(strict_types=1);

namespace Phosagro\Rating;

final class RatingItem
{
    public function __construct(
        public readonly int $eventIdentifier,
        public readonly \DateTimeImmutable $ratingDate,
        public readonly int $ratingIdentifier,
        public readonly \DateTimeImmutable $ratingPeriod,
        public readonly int $ratingScore,
        public readonly RatingType $ratingType,
        public readonly int $userIdentifier,
    ) {}
}
