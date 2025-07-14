<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class TaskFiles
{
    /**
     * @param string[] $filesTypes
     */
    public function __construct(
        public readonly int $filesCount,
        public readonly array $filesTypes,
    ) {}
}
