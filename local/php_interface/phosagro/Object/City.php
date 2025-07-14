<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class City
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly int $ownerId,
    ) {}

    public function toApi(): array
    {
        return [
            'id' => $this->code,
            'name' => $this->name,
        ];
    }
}
