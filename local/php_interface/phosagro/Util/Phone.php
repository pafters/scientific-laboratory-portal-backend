<?php

declare(strict_types=1);

namespace Phosagro\Util;

final class Phone
{
    public static function normalizePhone(string $value): string
    {
        $parsed = trim($value);

        $plus = str_starts_with($parsed, '+');

        $parsed = Text::replace($parsed, '~\D+~');

        $length = Text::length($parsed);

        if (!$plus) {
            if (11 === $length) {
                $parsed = Text::replace($parsed, '~^8~', '7');
            }

            if (10 === $length) {
                $parsed = "7{$parsed}";
            }
        }

        if (11 !== $length) {
            throw new \RuntimeException('Wrong phone format.');
        }

        return "+{$parsed}";
    }

    public static function tryNormalizePhone(string $value): ?string
    {
        try {
            return self::normalizePhone($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
