<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class TaskFile
{
    public function __construct(
        public readonly string $content,
        public readonly string $name,
    ) {}
}
