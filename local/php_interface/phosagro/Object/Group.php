<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class Group
{
    public function __construct(
        public readonly string $groupCode,
        public readonly int $groupIdentifier,
        public readonly string $groupName,
        public readonly int $groupOwnerId,
    ) {}

    public function toApi(): array
    {
        return [
            'id' => sprintf('%d', $this->groupIdentifier),
            'name' => $this->groupName,
        ];
    }
}
