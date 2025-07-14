<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class DatabaseChanges
{
    /**
     * @param array[]          $added
     * @param array<int,array> $changed
     * @param int[]            $deleted
     */
    public function __construct(
        public readonly array $added = [],
        public readonly array $changed = [],
        public readonly array $deleted = [],
    ) {}
}
