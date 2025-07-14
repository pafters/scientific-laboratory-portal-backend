<?php

declare(strict_types=1);

namespace Phosagro\Converter;

use Phosagro\Event\EventScore\EventScoreTotalRetriever;
use Phosagro\Event\EventScore\EventScoreUserRetriever;
use Phosagro\Event\EventState\EventStateRetriever;
use Phosagro\Event\TaskStatus\TaskStatusRetriever;
use Phosagro\Manager\TaskManager;
use Phosagro\Object\Bitrix\User;
use Phosagro\Object\Event;
use Phosagro\Object\Task;
use Phosagro\Util\Text;

final class UserEventToApiConverter
{
    public function __construct(
        private readonly DateTimeToApiConverter $dateConverter,
        private readonly EventForUser $eventForUser,
        private readonly EventScoreTotalRetriever $eventScoreTotalRetriever,
        private readonly EventScoreUserRetriever $eventScoreUserRetriever,
        private readonly EventStateRetriever $eventStateRetriever,
        private readonly TaskManager $tasks,
        private readonly TaskStatusRetriever $statuses,
    ) {}

    /**
     * @param Event[] $eventList
     */
    public function convertUserEventsToApi(array $eventList, User $user, bool $slim = false): array
    {
        if ($slim) {
            return array_values(array_map(
                fn (Event $event): array => $this->eventForUser->buildEventForUser($event, true),
                $eventList
            ));
        }

        $eventListData = [];

        $eventIdentifierList = array_map(static fn (Event $e): int => $e->id, $eventList);
        $eventIdentifierList = array_values(array_unique($eventIdentifierList));

        /** @var array<int,Task[]> $taskIndex */
        $taskIndex = [];

        $taskList = $this->tasks->findAllElements([
            'ACTIVE' => 'Y',
            'PROPERTY_EVENT' => $eventIdentifierList,
        ]);

        foreach ($taskList as $task) {
            $taskIndex[$task->eventIdentifier] ??= [];
            $taskIndex[$task->eventIdentifier][] = $task;
        }

        $canIndex = $this->eventScoreTotalRetriever->buildTotalScoreIndex($eventList);
        $gotIndex = $this->eventScoreUserRetriever->buildUserScoreIndex($eventList, $user);
        $stateIndex = $this->eventStateRetriever->buildStateIndex($eventList);
        $taskStatusIndex = $this->statuses->buildTaskStatusIndex($taskList, $user);

        foreach ($eventList as $event) {
            $eventTaskList = $taskIndex[$event->id] ?? [];

            $remaining = $stateIndex[$event]->remaining ?? new \DateInterval('PT0S');

            $eventListData[] = [
                'event' => $this->eventForUser->buildEventForUser($event),
                'remaining' => $this->dateConverter->convertDateInterval($remaining),
                'score' => [
                    'can' => $canIndex[$event],
                    'got' => $gotIndex[$event][$user],
                ],
                'state' => Text::lower($stateIndex[$event]->state->name),
                'tasks' => array_map(static fn (Task $task): array => [
                    'status' => [
                        'code' => $taskStatusIndex[$user][$task]->getStatus()->value,
                    ],
                ], $eventTaskList),
            ];
        }

        return $eventListData;
    }
}
