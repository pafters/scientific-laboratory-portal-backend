<?php

declare(strict_types=1);

namespace Phosagro\Event\Status;

use Phosagro\Event\TaskStatus\TaskStatusRetriever;
use Phosagro\Object\Bitrix\User;
use Phosagro\Object\Task;
use Phosagro\Object\TaskStatus;
use Phosagro\Object\TaskStatusReason;

final class StatusRetriever
{
    /** @var \WeakMap<User,\WeakMap<Task,TaskStatusReason>> */
    private \WeakMap $result;

    public function __construct(
        private readonly TaskStatusRetriever $taskStatusRetriever,
    ) {
        $this->result = new \WeakMap();
    }

    /**
     * @param array<int,Task>      $taskList
     * @param array<int,User>|User $userList
     */
    public function loadTaskStatus(array $taskList, array|User $userList): void
    {
        $this->result = $this->taskStatusRetriever->buildTaskStatusIndex($taskList, $userList);
    }

    public function retrieveTaskStatus(Task $task, User $user): TaskStatus
    {
        $reason = $this->result[$user][$task] ?? null;

        if (null === $reason) {
            return TaskStatus::UNAVAILABLE;
        }

        return $reason->getStatus();
    }

    public function retrieveTaskStatusReason(Task $task, User $user): ?TaskStatusReason
    {
        return $this->result[$user][$task] ?? null;
    }
}
