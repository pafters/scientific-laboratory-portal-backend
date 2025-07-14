<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class Event
{
    /**
     * @param int[] $galleryPictureIdentifiers
     * @param int[] $participantGroupIdentifiers
     * @param int[] $partnerIdentifiers
     */
    public function __construct(
        public readonly bool $active = false,
        public readonly ?\DateTimeImmutable $activeFrom = null,
        public readonly ?\DateTimeImmutable $activeTo = null,
        public readonly ?AgeCategory $ageCategory = null,
        public readonly ?\DateTimeImmutable $applicationEndsAt = null,
        public readonly bool $archived = false,
        public readonly ?City $city = null,
        public readonly ?int $detailPictureIdentifier = null,
        public readonly ?\DateTimeImmutable $endAt = null,
        public readonly string $for = '',
        public readonly string $fullText = '',
        public readonly array $galleryPictureIdentifiers = [],
        public readonly int $id = 0,
        public readonly int $moderatorIdentifier = 0,
        public readonly string $name = '',
        public readonly array $participantGroupIdentifiers = [],
        public readonly array $partnerIdentifiers = [],
        public readonly int $points = 0,
        public readonly ?int $previewPictureIdentifier = null,
        public readonly string $shortText = '',
        public readonly ?\DateTimeImmutable $startAt = null,
    ) {}

    public function getActualApplicationEndsAt(): ?\DateTimeImmutable
    {
        return $this->applicationEndsAt ?? $this->startAt;
    }

    public function getActualPreviewPictureIdentifier(): ?int
    {
        return $this->previewPictureIdentifier ?? $this->detailPictureIdentifier;
    }
}
