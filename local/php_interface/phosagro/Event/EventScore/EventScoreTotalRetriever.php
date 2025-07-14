<?php

declare(strict_types=1);

namespace Phosagro\Event\EventScore;

use Phosagro\Manager\TaskManager;
use Phosagro\Object\Event;
use Phosagro\Object\Task;

final class EventScoreTotalRetriever
{
    public function __construct(
        private readonly TaskManager $tasks,
    ) {}

    /**
     * @param Event|Event[] $eventList
     *
     * @return \WeakMap<Event,int>
     */
    public function buildTotalScoreIndex(array|Event $eventList): \WeakMap
    {
        /** @var \WeakMap<Event,int> $result */
        $result = new \WeakMap();

        if ($eventList instanceof Event) {
            $eventList = [$eventList];
        }

        $eventIdentifierList = array_map(static fn (Event $event): int => $event->id, $eventList);
        $eventIdentifierList = array_values(array_unique($eventIdentifierList));
        sort($eventIdentifierList, SORT_NUMERIC);

        /** @var array<int,Task[]> $taskIndex */
        $taskIndex = [];

        $taskList = $this->tasks->findAllElements(['ACTIVE' => 'Y', 'PROPERTY_EVENT' => $eventIdentifierList]);

        foreach ($taskList as $task) {
            $taskIndex[$task->eventIdentifier] ??= [];
            $taskIndex[$task->eventIdentifier][] = $task;
        }

        foreach ($eventList as $event) {
            $result[$event] = $event->points;
            foreach ($taskIndex[$event->id] ?? [] as $eventTask) {
                $result[$event] += $eventTask->taskBonus;
            }
        }

        return $result;
    }
}
