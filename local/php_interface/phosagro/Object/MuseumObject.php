<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class MuseumObject
{
    /**
     * @param MuseumObjectStatus[] $museumObjectStatusList
     */
    public function __construct(
        public readonly string $museumObjectCode,
        public readonly int $museumObjectIdentifier,
        public readonly string $museumObjectName,
        public readonly array $museumObjectStatusList,
    ) {}
}
