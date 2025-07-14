<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class VotingAnswer
{
    public function __construct(
        public readonly \DateTimeImmutable $answerAnsweredAt,
        public readonly int $answerIdentifier,
        public readonly int $answerUserIdentifier,
        public readonly int $answerVariantIdentifier,
    ) {}
}
