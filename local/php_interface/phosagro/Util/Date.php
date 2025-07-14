<?php

declare(strict_types=1);

namespace Phosagro\Util;

final class Date
{
    public static function fromFormat(string $value, DateFormat ...$formats): \DateTimeImmutable
    {
        foreach ($formats as $format) {
            $result = \DateTimeImmutable::createFromFormat($format->value, $value);

            if (false === $result) {
                continue;
            }

            if ($format->isTimeless()) {
                return $result->setTime(0, 0);
            }

            return $result;
        }

        throw new \ValueError(sprintf('Wrong format of the date "%s".', $value));
    }

    public static function toFormat(\DateTimeImmutable $value, DateFormat $format): string
    {
        return $value->format($format->value);
    }

    public static function tryFromFormat(string $value, DateFormat ...$formats): ?\DateTimeImmutable
    {
        try {
            return self::fromFormat($value, ...$formats);
        } catch (\ValueError) {
            return null;
        }
    }
}
