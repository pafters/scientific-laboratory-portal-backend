<?php

declare(strict_types=1);

namespace Phosagro\Event\TaskStatus;

use Phosagro\Event\Task\CompletionChecker;
use Phosagro\Event\Task\CompletionStatus;
use Phosagro\Manager\CompletionManager;
use Phosagro\Manager\ParticipantManager;
use Phosagro\Object\Bitrix\User;
use Phosagro\Object\Completion;
use Phosagro\Object\Participant;
use Phosagro\Object\Task;
use Phosagro\Object\TaskStatusReason;
use Phosagro\System\Clock;

final class TaskStatusRetriever
{
    public function __construct(
        private readonly Clock $clock,
        private readonly CompletionChecker $completionChecker,
        private readonly CompletionManager $completions,
        private readonly ParticipantManager $participants,
    ) {}

    /**
     * @param array<int,Task>|Task $taskList
     * @param array<int,User>|User $userList
     *
     * @return \WeakMap<User,\WeakMap<Task,TaskStatusReason>>
     */
    public function buildTaskStatusIndex(array|Task $taskList, array|User $userList): \WeakMap
    {
        /** @var \WeakMap<User,\WeakMap<Task,TaskStatusReason>> */
        $result = new \WeakMap();

        if ($taskList instanceof Task) {
            $taskList = [$taskList];
        }

        /** @var array<int,int> $taskIdentifierIndex */
        $taskIdentifierIndex = [];

        foreach ($taskList as $task) {
            $taskIdentifierIndex[$task->taskIdentifier] = $task->taskIdentifier;
        }

        /** @var array<int,int> $eventIdentifierIndex */
        $eventIdentifierIndex = [];

        foreach ($taskList as $task) {
            $eventIdentifierIndex[$task->eventIdentifier] = $task->eventIdentifier;
        }

        if ($userList instanceof User) {
            $userList = [$userList];
        }

        /** @var array<int,int> $userIdentifierIndex */
        $userIdentifierIndex = [];

        foreach ($userList as $user) {
            $userIdentifierIndex[$user->userIdentifier] = $user->userIdentifier;
        }

        /** @var array<int,array<int,Completion>> $completionIndex */
        $completionIndex = [];

        /** @var array<int,array<int,Participant>> $participantIndex */
        $participantIndex = [];

        /** @var array<int,int> $participantIdentifierIndex */
        $participantIdentifierIndex = [];

        $participantList = $this->participants->findAllElements([
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
            'PROPERTY_EVENT' => array_values($eventIdentifierIndex),
            'PROPERTY_USER' => array_values($userIdentifierIndex),
        ]);

        foreach ($participantList as $participant) {
            $participantIdentifierIndex[$participant->participantIdentifier] = $participant->participantIdentifier;
            $participantIndex[$participant->eventIdentifier][$participant->userIdentifier] = $participant;
        }

        if (([] !== $taskIdentifierIndex) && ([] !== $participantIdentifierIndex)) {
            $completionList = $this->completions->findAllElements([
                'PROPERTY_TASK' => array_values($taskIdentifierIndex),
                'PROPERTY_PARTICIPANT' => array_values($participantIdentifierIndex),
            ]);

            foreach ($completionList as $completion) {
                $completionIndex[$completion->taskIdentifier][$completion->participantIdentifier] = $completion;
            }
        }

        $now = $this->clock->now();

        foreach ($userList as $user) {
            $result[$user] = new \WeakMap();

            $stop = false;

            foreach ($taskList as $task) {
                if ($stop) {
                    $result[$user][$task] = TaskStatusReason::NOT_COMPLETED_PREVIOUS_REQUIRED;

                    continue;
                }

                $participant = $participantIndex[$task->eventIdentifier][$user->userIdentifier] ?? null;

                if (null === $participant) {
                    $result[$user][$task] = TaskStatusReason::NOT_A_PARTICIPANT;

                    continue;
                }

                $completion = $completionIndex[$task->taskIdentifier][$participant->participantIdentifier] ?? null;

                if (null === $completion) {
                    if ($participant->refused) {
                        $result[$user][$task] = TaskStatusReason::PARTICIPANT_REFUSED;

                        continue;
                    }

                    if ((null !== $task->taskStarts) && ($task->taskStarts > $now)) {
                        $result[$user][$task] = TaskStatusReason::TASK_IS_NOT_STARTED;

                        $stop = true;

                        continue;
                    }

                    if ((null !== $task->taskEnds) && ($task->taskEnds <= $now)) {
                        $result[$user][$task] = TaskStatusReason::TASK_IS_ENDED;

                        if ($task->taskRequired) {
                            $stop = true;
                        }

                        continue;
                    }

                    if (!$task->taskActive) {
                        $result[$user][$task] = TaskStatusReason::TASK_IS_CLOSED;

                        if ($task->taskRequired) {
                            $stop = true;
                        }

                        continue;
                    }

                    $result[$user][$task] = TaskStatusReason::ALL_GOOD;

                    if ($task->taskRequired) {
                        $stop = true;
                    }

                    continue;
                }

                $result[$user][$task] = match ($this->completionChecker->checkCompletion($completion, $task)) {
                    CompletionStatus::REJECTED_BY_MODERATOR_ERROR => TaskStatusReason::TASK_FORM_IS_MISSING,
                    CompletionStatus::REJECTED_BY_WRONG_ANSWERS => TaskStatusReason::TASK_FORM_IS_WRONG,
                    CompletionStatus::REJECTED_BY_WRONG_FILES => TaskStatusReason::TASK_FILES_IS_REJECTED,
                    CompletionStatus::MODERATING => TaskStatusReason::TASK_IS_ON_MODERATION,
                    CompletionStatus::ACCEPTED => TaskStatusReason::TASK_IS_COMPLETED,
                };

                if (TaskStatusReason::TASK_IS_ON_MODERATION !== $result[$user][$task]) {
                    continue;
                }

                if ($task->taskRequired) {
                    $stop = true;
                }
            }
        }

        return $result;
    }
}
