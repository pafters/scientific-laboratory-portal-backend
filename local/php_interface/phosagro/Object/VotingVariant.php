<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class VotingVariant
{
    public function __construct(
        public readonly int $variantIdentifier,
        public readonly int $variantSort,
        public readonly string $variantText,
    ) {}
}
