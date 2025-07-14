<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class Partner
{
    public function __construct(
        public readonly string $code,
        public readonly string $color,
        public readonly string $name,
        public readonly int $ownerId,
    ) {}

    public function toApi(): array
    {
        $result = [
            'id' => $this->code,
            'name' => $this->name,
        ];

        if ('' !== $this->color) {
            $result['color'] = $this->color;
        }

        ksort($result, SORT_STRING);

        return $result;
    }
}
