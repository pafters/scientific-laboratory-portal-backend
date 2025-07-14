<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class TaskFormFieldVariant
{
    public function __construct(
        public readonly bool $variantCorrect,
        public readonly int $variantIdentifier,
        public readonly int $variantSort,
        public readonly string $variantTitle,
    ) {}
}
