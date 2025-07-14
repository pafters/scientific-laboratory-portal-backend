<?php

declare(strict_types=1);

namespace Phosagro\Event\EventScore;

use Phosagro\Object\Bitrix\User;
use Phosagro\Object\Event;
use Phosagro\Rating\RatingCalculator;
use Phosagro\Rating\RatingType;
use Phosagro\Rating\RatingUpdater;

final class EventScoreUserRetriever
{
    public function __construct(
        private readonly RatingCalculator $ratingCalculator,
        private readonly RatingUpdater $ratingUpdater,
    ) {}

    /**
     * @param Event|Event[] $eventList
     * @param User|User[]   $userList
     *
     * @return \WeakMap<Event,\WeakMap<User,int>>
     */
    public function buildUserScoreIndex(array|Event $eventList, array|User $userList): \WeakMap
    {
        /** @var \WeakMap<Event,\WeakMap<User,int>> $result */
        $result = new \WeakMap();

        if ($eventList instanceof Event) {
            $eventList = [$eventList];
        }

        $eventIdentifiierList = array_map(static fn (Event $event): int => $event->id, $eventList);
        $eventIdentifiierList = array_values(array_unique($eventIdentifiierList));
        sort($eventIdentifiierList, SORT_NUMERIC);

        if ($userList instanceof User) {
            $userList = [$userList];
        }

        $userIdentifierList = array_map(static fn (User $user): int => $user->userIdentifier, $userList);
        $userIdentifierList = array_values(array_unique($userIdentifierList));
        sort($userIdentifierList, SORT_NUMERIC);

        $this->ratingUpdater->updateRating();

        $scoreIndex = $this->ratingCalculator->buildScoreIndex(
            RatingType::EVENT,
            $eventIdentifiierList,
            $userIdentifierList,
        );

        foreach ($eventList as $event) {
            $result[$event] = new \WeakMap();
            foreach ($userList as $user) {
                $ratingItem = $scoreIndex[RatingType::EVENT->value][$event->id][$user->userIdentifier] ?? null;
                $result[$event][$user] = $ratingItem?->ratingScore ?? 0;
            }
        }

        return $result;
    }
}
