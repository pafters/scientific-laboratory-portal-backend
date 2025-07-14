<?php

declare(strict_types=1);

namespace Phosagro\Converter;

use Phosagro\Manager\AgeCategoryManager;
use Phosagro\Manager\EventManager;
use Phosagro\Object\AgeCategory;
use Phosagro\Object\Event;
use Phosagro\Object\Voting;
use Phosagro\Object\VotingVariant;
use Phosagro\System\Clock;
use Phosagro\System\ImageManager;
use Phosagro\Util\Date;
use Phosagro\Util\DateFormat;
use WeakMap;

final class VotingToApiConverter
{
    public function __construct(
        private readonly AgeCategoryManager $ages,
        private readonly Clock $clock,
        private readonly EventManager $events,
        private readonly ImageManager $images,
    ) {}

    /**
     * @param Voting[] $votingList
     *
     * @return \WeakMap<Voting,array>
     */
    public function convertVotingListToApi(array $votingList, bool $full = false): \WeakMap
    {
        /** @var \WeakMap<Voting,array> $result */
        $result = new \WeakMap();

        $now = $this->clock->now();
        $ageIndex = $this->getAgeCategoryIndex($full ? $votingList : []);
        $eventIndex = $this->getEventIdex($full ? $votingList : []);

        foreach ($votingList as $voting) {
            $item = [
                'ends' => Date::toFormat($voting->votingEndsAt, DateFormat::BITRIX),
                'id' => sprintf('%d', $voting->votingIdentifier),
                'name' => $voting->votingName,
                'starts' => Date::toFormat($voting->votingStartsAt, DateFormat::BITRIX),
            ];

            if ('' !== $voting->votingBrief) {
                $item['brief'] = $voting->votingBrief;
            }

            $thumbnailIdentifier = $voting->getActualThumbnailIdentifier();

            if (null !== $thumbnailIdentifier) {
                $item['thumbnail'] = $this->images->resizeImage($thumbnailIdentifier, 'voting', 'thumbnail');
            }

            if ($full) {
                $age = $ageIndex[$voting] ?? null;

                if (null === $age) {
                    throw new \RuntimeException(sprintf('Not found age category %u.', $voting->votingAgeCategoryIdentifier));
                }

                $item['age'] = $this->convertAgeCategoryForVoting($age);

                if ('' !== $voting->votingDescription) {
                    $item['description'] = $voting->votingDescription;
                }

                $item['ended'] = $voting->isEnded($now);

                $event = $eventIndex[$voting] ?? null;

                if (null !== $event) {
                    $item['event'] = $this->convertEventForVoting($event);
                }

                $item['limit'] = $voting->votingVariantLimit;

                $pictureIdentifier = $voting->getActualPictureIdentifier();

                if (null !== $pictureIdentifier) {
                    $item['picture'] = $this->images->resizeImage($pictureIdentifier, 'voting', 'detail');
                }

                $item['variants'] = array_map($this->convertVotingVariant(...), $voting->votingVariantList);
            }

            ksort($item, SORT_STRING);

            $result[$voting] = $item;
        }

        return $result;
    }

    public function convertVotingToApi(Voting $voting): array
    {
        return $this->convertVotingListToApi([$voting], true)[$voting];
    }

    private function convertAgeCategoryForVoting(AgeCategory $ageCategory): array
    {
        return [
            'id' => sprintf('%d', $ageCategory->ageCategoryIdentifier),
            'name' => $ageCategory->name,
        ];
    }

    private function convertEventForVoting(Event $event): array
    {
        return [
            'id' => sprintf('%d', $event->id),
            'name' => $event->name,
        ];
    }

    private function convertVotingVariant(VotingVariant $variant): array
    {
        return [
            'id' => sprintf('%d', $variant->variantIdentifier),
            'name' => $variant->variantText,
        ];
    }

    /**
     * @param Voting[] $votingList
     *
     * @return \WeakMap<Voting,AgeCategory>
     */
    private function getAgeCategoryIndex(array $votingList): \WeakMap
    {
        /** @var \WeakMap<Voting,AgeCategory> $result */
        $result = new \WeakMap();

        foreach ($votingList as $voting) {
            $ageCategory = $this->ages->findOne($voting->votingAgeCategoryIdentifier);
            if (null !== $ageCategory) {
                $result[$voting] = $ageCategory;
            }
        }

        return $result;
    }

    /**
     * @param Voting[] $votingList
     *
     * @return WeakMap<Voting,Event>,
     */
    private function getEventIdex(array $votingList): \WeakMap
    {
        /** @var \WeakMap<Voting,Event> */
        $result = new \WeakMap();

        /** @var array<int,Event> $eventIndex */
        $eventIndex = [];

        /** @var array<int,null> $identifierIndex */
        $identifierIndex = [];

        foreach ($votingList as $voting) {
            if (null !== $voting->votingEventIdentifier) {
                $identifierIndex[$voting->votingEventIdentifier] = null;
            }
        }

        /** @var int[] $identifierList */
        $identifierList = array_keys($identifierIndex);

        sort($identifierList, SORT_NUMERIC);

        if ([] !== $identifierList) {
            foreach ($this->events->findAllElements(['ID' => $identifierList]) as $event) {
                $eventIndex[$event->id] = $event;
            }
        }

        foreach ($votingList as $voting) {
            if (null !== $voting->votingEventIdentifier) {
                $event = ($eventIndex[$voting->votingEventIdentifier] ?? null);
                if (null !== $event) {
                    $result[$voting] = $event;
                }
            }
        }

        return $result;
    }
}
