<?php

declare(strict_types=1);

namespace Phosagro\System;

use Phosagro\Util\Text;

final class UrlManager
{
    private string $host;

    public function __construct()
    {
        $https = ($_SERVER['HTTPS'] ?? null);
        $https = (\is_string($https) ? Text::lower($https) : '');
        $protocol = (('' !== $https) && ('off' !== $https) ? 'https' : 'http');
        $host = ($_SERVER['SERVER_NAME'] ?? 'localhost');

        $this->host = "{$protocol}://{$host}";
    }

    public function makeAbsolute(string $path): string
    {
        return $this->host.$path;
    }
}
