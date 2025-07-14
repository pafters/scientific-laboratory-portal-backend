<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class Task
{
    public function __construct(
        public readonly ?int $correctAnswerLimit,
        public readonly int $eventIdentifier,
        public readonly TaskFiles $filesData,
        public readonly ?TaskForm $formData,
        public readonly TaskPlace $placeData,
        public readonly bool $taskActive,
        public readonly int $taskBonus,
        public readonly string $taskDescription,
        public readonly int $taskDuration,
        public readonly ?\DateTimeImmutable $taskEnds,
        public readonly int $taskIdentifier,
        public readonly string $taskName,
        public readonly bool $taskRequired,
        public readonly ?\DateTimeImmutable $taskStarts,
        public readonly TaskType $taskType,
        public readonly TaskVideo $videoData,
    ) {}

    public function __toString(): string
    {
        return sprintf('[%s] %s', $this->taskIdentifier, $this->taskName);
    }
}
