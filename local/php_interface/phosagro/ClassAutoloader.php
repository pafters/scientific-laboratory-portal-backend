<?php

declare(strict_types=1);

namespace Phosagro;

final class ClassAutoloader
{
    public static function register(): void
    {
        spl_autoload_register(static function (string $class): void {
            $parts = explode('\\', $class);

            if ((\count($parts) < 2) || ('Phosagro' !== $parts[array_key_first($parts)])) {
                return;
            }

            foreach ($parts as $name) {
                if (1 !== preg_match('~^[A-Z][0-9A-Za-z]+$~', $name)) {
                    return;
                }
            }

            $parts[array_key_first($parts)] = __DIR__;
            $parts[array_key_last($parts)] = ($parts[array_key_last($parts)].'.php');

            $path = implode(\DIRECTORY_SEPARATOR, $parts);

            if (file_exists($path)) {
                require $path;
            }
        });
    }
}
