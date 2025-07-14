<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class PhosagroCompany
{
    public function __construct(
        public readonly int $bitrixId,
        public readonly string $name,
        public readonly int $ownerId,
    ) {}

    public function toApi(): array
    {
        return [
            'id' => sprintf('%d', $this->bitrixId),
            'name' => $this->name,
        ];
    }
}
