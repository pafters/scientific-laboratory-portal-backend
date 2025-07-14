<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class AccrualReason
{
    public function __construct(
        public readonly string $reasonCode,
        public readonly int $reasonIdentifier,
        public readonly string $reasonName,
    ) {}

    public function __toString(): string
    {
        return sprintf('[%d] %s', $this->reasonIdentifier, $this->reasonName);
    }
}
