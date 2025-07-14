<?php

declare(strict_types=1);

namespace Phosagro\Converter;

final class DateTimeToApiConverter
{
    public function convertDateInterval(\DateInterval $interval): array
    {
        return [
            'days' => $interval->days ?: 0,
            'hours' => $interval->h,
            'minutes' => $interval->i,
            'seconds' => $interval->s,
        ];
    }

    public function convertDateTimeToApi(\DateTimeImmutable $dateTime): array
    {
        return [
            'day' => (int) $dateTime->format('j'),
            'hour' => (int) $dateTime->format('G'),
            'minute' => (int) $dateTime->format('i'),
            'month' => (int) $dateTime->format('n'),
            'second' => (int) $dateTime->format('s'),
            'year' => (int) $dateTime->format('Y'),
        ];
    }
}
