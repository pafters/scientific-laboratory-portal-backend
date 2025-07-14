<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class MuseumVisit
{
    public function __construct(
        public readonly bool $museumVisitAccrued,
        public readonly \DateTimeImmutable $museumVisitDate,
        public readonly int $museumVisitIdentifier,
        public readonly int $museumVisitObjectIdentifier,
        public readonly string $museumVisitStatus,
        public readonly int $museumVisitUserIdentifier,
        public readonly string $museumVisitVisit,
    ) {}
}
