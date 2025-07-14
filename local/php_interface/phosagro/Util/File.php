<?php

declare(strict_types=1);

namespace Phosagro\Util;

final class File
{
    public static function delete(string $path): void
    {
        $result = unlink($path);

        if (!$result) {
            throw new \RuntimeException(sprintf('Can not delete "%s".', $path));
        }
    }

    public static function read(string $path): string
    {
        $content = file_get_contents($path);

        if (false === $content) {
            throw new \RuntimeException(sprintf('Can not read "%s".', $path));
        }

        return $content;
    }

    public static function readInput(): string
    {
        return self::read('php://input');
    }

    public static function write(string $path, string $content, ?string $separator = null): void
    {
        $flags = 0;

        if (null !== $separator) {
            $content .= $separator;
            $flags |= FILE_APPEND;
        }

        $result = file_put_contents($path, $content, $flags);

        if (false === $result) {
            throw new \RuntimeException(sprintf('Can not write "%s".', $path));
        }
    }
}
