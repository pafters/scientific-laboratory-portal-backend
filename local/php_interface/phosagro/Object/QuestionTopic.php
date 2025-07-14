<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class QuestionTopic
{
    public function __construct(
        public readonly int $questionTopicIdentifier,
        public readonly string $questionTopicName,
    ) {}

    public function toApi(): array
    {
        return [
            'id' => sprintf('%d', $this->questionTopicIdentifier),
            'name' => $this->questionTopicName,
        ];
    }
}
