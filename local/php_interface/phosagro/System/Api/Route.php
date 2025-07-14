<?php

declare(strict_types=1);

namespace Phosagro\System\Api;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Route
{
    public function __construct(
        public string $pattern,
        public string $method = 'GET',
    ) {}
}
