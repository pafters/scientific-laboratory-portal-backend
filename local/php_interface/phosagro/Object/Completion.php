<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class Completion
{
    public function __construct(
        public readonly int $answerIdentifier,
        public readonly bool $completionActive,
        public readonly int $completionIdentifier,
        public readonly bool $filesRejeted,
        public readonly int $participantIdentifier,
        public readonly int $taskIdentifier,
    ) {}

    public function __toString(): string
    {
        return sprintf('[%d]', $this->completionIdentifier);
    }
}
