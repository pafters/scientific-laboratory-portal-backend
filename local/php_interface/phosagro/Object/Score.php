<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class Score
{
    public function __construct(
        public readonly int $reasonIdentifier,
        public readonly \DateTimeImmutable $scoreAddedAt,
        public readonly int $scoreAmount,
        public readonly string $scoreComment,
        public readonly int $scoreIdentifier,
        public readonly string $scoreSubject,
        public readonly int $userIdentifier,
    ) {}
}
