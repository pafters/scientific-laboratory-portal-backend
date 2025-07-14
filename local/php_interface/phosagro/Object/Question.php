<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class Question
{
    public function __construct(
        public readonly \DateTimeImmutable $questionChangedAt,
        public readonly \DateTimeImmutable $questionCreatedAt,
        public readonly ?int $questionEventIdentifier,
        public readonly ?int $questionFileIdentifier,
        public readonly int $questionIdentifier,
        public readonly int $questionModeratorIdentifier,
        public readonly string $questionName,
        public readonly string $questionQuestion,
        public readonly string $questionResponse,
        public readonly int $questionSort,
        public readonly int $questionTopicIdentifier,
        public readonly QuestionType $questionType,
        public readonly string $questionUrl,
        public readonly int $questionUserIdentifier,
    ) {}
}
