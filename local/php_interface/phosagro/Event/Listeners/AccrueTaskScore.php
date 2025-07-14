<?php

declare(strict_types=1);

namespace Phosagro\Event\Listeners;

use Bitrix\Main\EventManager as BitrixEventManager;
use Phosagro\Enum\LogEvent;
use Phosagro\Event\Status\StatusRetriever;
use Phosagro\Event\Task\CompletionChecker;
use Phosagro\Event\Task\CompletionStatus;
use Phosagro\Iblocks;
use Phosagro\Log\Logger;
use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\Manager\CompletionManager;
use Phosagro\Manager\Errors\NotFoundException;
use Phosagro\Manager\EventManager;
use Phosagro\Manager\ParticipantManager;
use Phosagro\Manager\ScoreManager;
use Phosagro\Manager\TaskManager;
use Phosagro\Object\AccrualReasonCode;
use Phosagro\Object\Bitrix\User;
use Phosagro\Object\Event;
use Phosagro\Object\Task;
use Phosagro\Object\TaskStatus;
use Phosagro\System\ListenerInterface;

final class AccrueTaskScore implements ListenerInterface
{
    public function __construct(
        private readonly CompletionChecker $completionChecker,
        private readonly CompletionManager $completions,
        private readonly EventManager $events,
        private readonly Logger $logger,
        private readonly ParticipantManager $participants,
        private readonly ScoreManager $scores,
        private readonly StatusRetriever $statuses,
        private readonly TaskManager $tasks,
        private readonly UserManager $users,
    ) {}

    public function registerListeners(BitrixEventManager $eventManager): void
    {
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementAdd', $this->executeListener(...));
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementUpdate', $this->executeListener(...));
    }

    private function accrueForEvent(User $user, Event $event): void
    {
        $eventTaskList = $this->tasks->findAllElements(['ACTIVE' => 'Y', 'PROPERTY_EVENT' => $event->id]);

        $this->statuses->loadTaskStatus($eventTaskList, $user);

        foreach ($eventTaskList as $eventTask) {
            $status = $this->statuses->retrieveTaskStatus($eventTask, $user);

            if (TaskStatus::COMPLETED !== $status) {
                return;
            }
        }

        if ($this->scores->hasScore($user, AccrualReasonCode::EVENT_COMPLETION, $event)) {
            return;
        }

        $this->scores->addScore($user, AccrualReasonCode::EVENT_COMPLETION, $event, $event->points);
    }

    private function accrueForTask(User $user, Task $task): void
    {
        if ($this->scores->hasScore($user, AccrualReasonCode::TASK_COMPLETION, $task)) {
            return;
        }

        $this->scores->addScore($user, AccrualReasonCode::TASK_COMPLETION, $task, $task->taskBonus);
    }

    private function executeListener(array $fields): void
    {
        $iblockId = (int) ($fields['IBLOCK_ID'] ?? 0);

        if ($iblockId !== Iblocks::completionId()) {
            return;
        }

        $result = $fields['RESULT'] ?? false;

        if (!$result) {
            return;
        }

        $id = (int) ($fields['ID'] ?? 0);

        try {
            $completion = $this->completions->getSingleElement(['ID' => $id]);
            $task = $this->tasks->getSingleElement(['ID' => $completion->taskIdentifier]);
            $status = $this->completionChecker->checkCompletion($completion, $task);
            $event = $this->events->getEventByIdentifier($task->eventIdentifier);
            $participant = $this->participants->getSingleElement(['ID' => $completion->participantIdentifier]);
            $user = $this->users->getById($participant->userIdentifier);
        } catch (NotFoundException $error) {
            $this->logger->log(LogEvent::ACCRUE_TASK_SCORE_FAILED, sprintf('%d', $id), $error->getMessage());

            return;
        }

        if (!$completion->completionActive) {
            return;
        }

        if (CompletionStatus::ACCEPTED === $status) {
            $this->accrueForTask($user, $task);
        }

        $this->accrueForEvent($user, $event);
    }
}
