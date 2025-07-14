<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class AgeCategory
{
    public function __construct(
        public readonly int $ageCategoryIdentifier,
        public readonly ?int $maximalAge,
        public readonly int $minimalAge,
        public readonly string $name,
        public readonly int $ownerId,
    ) {}

    public function toApi(): array
    {
        $result = [
            'age' => $this->minimalAge,
            'id' => sprintf('%d', $this->ageCategoryIdentifier),
            'name' => $this->name,
        ];

        if (null !== $this->maximalAge) {
            $result['maximalAge'] = $this->maximalAge;
        }

        ksort($result, SORT_STRING);

        return $result;
    }
}
