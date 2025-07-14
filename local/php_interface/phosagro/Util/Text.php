<?php

declare(strict_types=1);

namespace Phosagro\Util;

use const Phosagro\UTF8;

final class Text
{
    public static function bitrix(string $text, string $type): string
    {
        return match ($type) {
            'html' => $text,
            'text' => htmlspecialchars($text),
        };
    }

    public static function brief(string $text, int $length = 100): string
    {
        if (self::length($text) <= $length) {
            return $text;
        }

        return self::substring($text, 0, $length - 1).'…';
    }

    public static function count(string $text, string $search): int
    {
        return mb_substr_count($text, $search, UTF8);
    }

    public static function length(string $text): int
    {
        return mb_strlen($text, UTF8);
    }

    public static function lower(string $text): string
    {
        return mb_strtolower($text, UTF8);
    }

    public static function match(string $text, string $pattern): ?array
    {
        $matches = [];

        if (1 !== preg_match($pattern, $text, $matches)) {
            return null;
        }

        return $matches;
    }

    public static function removePrefix(string $text, string $prefix): string
    {
        return self::replace($text, sprintf('~^%s~', preg_quote($prefix, '~')));
    }

    public static function replace(string $text, string $pattern, string $replacement = ''): string
    {
        $result = preg_replace($pattern, $replacement, $text);

        if (!\is_string($result)) {
            throw new \RuntimeException(sprintf('Replace failed for pattern "%s".', $pattern));
        }

        return $result;
    }

    public static function snake(string $text): string
    {
        return self::lower(self::replace($text, '~([a-z])([A-Z])~', '$1_$2'));
    }

    /**
     * @return string[]
     */
    public static function split(string $text, string $pattern): array
    {
        $result = preg_split($pattern, $text);

        if (false === $result) {
            throw new \RuntimeException(sprintf('Split failed for pattern "%s".', $pattern));
        }

        return $result;
    }

    public static function substring(string $text, int $start, ?int $length = null): string
    {
        return mb_substr($text, $start, $length, UTF8);
    }

    public static function title(string $text): string
    {
        /** @var string[] $wordList */
        $wordList = [];

        foreach (self::split($text, '~\b~') as $word) {
            $word = trim($word);
            if ('' !== $word) {
                $wordList[] = self::upperFirst($word);
            }
        }

        return implode(' ', $wordList);
    }

    public static function upper(string $text): string
    {
        return mb_strtoupper($text, UTF8);
    }

    public static function upperFirst(string $text): string
    {
        if (self::length($text) < 1) {
            return $text;
        }

        return self::upper(self::substring($text, 0, 1)).self::lower(self::substring($text, 1));
    }
}
