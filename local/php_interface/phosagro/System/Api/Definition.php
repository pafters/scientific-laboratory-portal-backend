<?php

declare(strict_types=1);

namespace Phosagro\System\Api;

final class Definition
{
    public function __construct(
        public readonly string $class,
        public readonly string $function,
        public readonly string $method,
        public readonly string $pattern,
    ) {}
}
