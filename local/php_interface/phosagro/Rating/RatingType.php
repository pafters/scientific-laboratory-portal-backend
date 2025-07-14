<?php

declare(strict_types=1);

namespace Phosagro\Rating;

use DateTimeImmutable;

enum RatingType: int
{
    case EVENT = 4;
    case MONTH = 3;
    case TOTAL = 1;
    case WEEK = 2;

    public function getPeriod(\DateTimeImmutable $now): \DateTimeImmutable
    {
        return match ($this) {
            default => new DateTimeImmutable('@0'),
            self::MONTH => $now->modify('first day of this month midnight'),
            self::WEEK => $now->modify('monday this week midnight')
        };
    }
}
