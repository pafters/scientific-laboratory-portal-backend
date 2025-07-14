<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class Voting
{
    /**
     * @param int[]           $votingFileIdentifiers
     * @param int[]           $votingGroupIdentifiers
     * @param VotingVariant[] $votingVariantList
     */
    public function __construct(
        public readonly bool $votingActive,
        public readonly int $votingAgeCategoryIdentifier,
        public string $votingBrief,
        public string $votingDescription,
        public readonly \DateTimeImmutable $votingEndsAt,
        public readonly ?int $votingEventIdentifier,
        public readonly array $votingFileIdentifiers,
        public readonly array $votingGroupIdentifiers,
        public readonly int $votingIdentifier,
        public readonly bool $votingMailed,
        public readonly string $votingName,
        public readonly int $votingOwnerIdentifier,
        public readonly ?int $votingPictureIdentifier,
        public readonly int $votingQuestionIdentifier,
        public readonly \DateTimeImmutable $votingStartsAt,
        public readonly int $votingSort,
        public readonly ?int $votingThumbnailIdentifier,
        public readonly int $votingVariantLimit,
        public array $votingVariantList,
    ) {}

    public function getActualPictureIdentifier(): ?int
    {
        return $this->votingPictureIdentifier ?? $this->votingThumbnailIdentifier;
    }

    public function getActualThumbnailIdentifier(): ?int
    {
        return $this->votingThumbnailIdentifier ?? $this->votingPictureIdentifier;
    }

    public function isEnded(\DateTimeImmutable $now): bool
    {
        if (!$this->votingActive) {
            return true;
        }

        if ($now < $this->votingStartsAt) {
            return true;
        }

        if ($this->votingEndsAt <= $now) {
            return true;
        }

        return false;
    }
}
