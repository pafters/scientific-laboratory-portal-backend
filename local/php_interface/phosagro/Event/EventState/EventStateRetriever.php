<?php

declare(strict_types=1);

namespace Phosagro\Event\EventState;

use Phosagro\Object\Event;
use Phosagro\Object\EventState;
use Phosagro\System\Clock;

final class EventStateRetriever
{
    public function __construct(
        private readonly Clock $clock,
    ) {}

    /**
     * @param Event|Event[] $eventList
     *
     * @return \WeakMap<Event,EventStateInfo>
     */
    public function buildStateIndex(array|Event $eventList): \WeakMap
    {
        /** @var \WeakMap<Event,EventStateInfo> $result */
        $result = new \WeakMap();

        if ($eventList instanceof Event) {
            $eventList = [$eventList];
        }

        $now = $this->clock->now();

        foreach ($eventList as $event) {
            if ((null !== $event->endAt) && ($event->endAt <= $now)) {
                $result[$event] = new EventStateInfo(null, EventState::COMPLETED);

                continue;
            }

            if ((null !== $event->startAt) && ($now < $event->startAt)) {
                $result[$event] = new EventStateInfo($event->startAt->diff($now) ?: null, EventState::PREPARING);

                continue;
            }

            if (null !== $event->endAt) {
                $result[$event] = new EventStateInfo($event->endAt->diff($now) ?: null, EventState::RUNNING);

                continue;
            }

            $result[$event] = new EventStateInfo(null, EventState::RUNNING);
        }

        return $result;
    }
}
