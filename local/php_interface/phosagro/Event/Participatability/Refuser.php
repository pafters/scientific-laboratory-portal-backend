<?php

declare(strict_types=1);

namespace Phosagro\Event\Participatability;

use Phosagro\Manager\ParticipantManager;
use Phosagro\Object\Bitrix\User;
use Phosagro\Object\Event;
use Phosagro\Object\Participant;
use Phosagro\System\Clock;

final class Refuser
{
    public function __construct(
        private readonly Clock $clock,
        private readonly ParticipantManager $participants,
    ) {}

    /**
     * @param Event|Event[] $eventList
     * @param User|User[]   $userList
     */
    public function refuse(array|Event $eventList, array|User $userList): void
    {
        if ($eventList instanceof Event) {
            $eventList = [$eventList];
        }

        if ($userList instanceof User) {
            $userList = [$userList];
        }

        $now = $this->clock->now();

        $participantIndex = $this->findParticipants($eventList, $userList);

        foreach ($eventList as $event) {
            $applicationEndsAt = $event->getActualApplicationEndsAt();

            if ((null === $applicationEndsAt) || ($applicationEndsAt > $now)) {
                foreach ($userList as $user) {
                    $participantToDelete = $participantIndex[$event][$user] ?? null;
                    if (null !== $participantToDelete) {
                        $this->participants->deleteParticipant($participantToDelete);
                    }
                }

                continue;
            }

            foreach ($userList as $user) {
                foreach ($userList as $user) {
                    $participantToRefuse = $participantIndex[$event][$user] ?? null;
                    if (null !== $participantToRefuse) {
                        $this->participants->refuseParticipant($participantToRefuse);
                    }
                }
            }
        }
    }

    /**
     * @param Event[] $eventList
     * @param User[]  $userList
     *
     * @return \WeakMap<Event,\WeakMap<User,Participant>>
     */
    private function findParticipants(array $eventList, array $userList): \WeakMap
    {
        $result = new \WeakMap();

        if (([] !== $eventList) && ([] !== $userList)) {
            /** @var array<int,array<int,Participant>> $index */
            $index = [];

            $found = $this->participants->findAllElements([
                'PROPERTY_EVENT' => array_map(static fn (Event $e): int => $e->id, $eventList),
                'PROPERTY_USER' => array_map(static fn (User $u): int => $u->userIdentifier, $userList),
            ]);

            foreach ($found as $participant) {
                $index[$participant->eventIdentifier] ??= [];
                $index[$participant->eventIdentifier][$participant->userIdentifier] = $participant;
            }
        }

        foreach ($eventList as $event) {
            $result[$event] ??= new \WeakMap();
            foreach ($userList as $user) {
                $participant = $index[$event->id][$user->userIdentifier] ?? null;
                if (null !== $participant) {
                    $result[$event][$user] = $participant;
                }
            }
        }

        return $result;
    }
}
