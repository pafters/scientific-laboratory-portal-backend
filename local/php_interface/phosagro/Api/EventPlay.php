<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Converter\TaskToApiConverter;
use Phosagro\Event\Status\StatusRetriever;
use Phosagro\Manager\EventManager;
use Phosagro\Manager\ParticipantManager;
use Phosagro\Manager\TaskManager;
use Phosagro\System\Api\Errors\NotAccessibleError;
use Phosagro\System\Api\Errors\NotAuthorizedError;
use Phosagro\System\Api\Errors\NotFoundError;
use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;

final class EventPlay
{
    public function __construct(
        private readonly AuthorizationContext $authorization,
        private readonly EventManager $events,
        private readonly ParticipantManager $participants,
        private readonly StatusRetriever $statuses,
        private readonly TaskToApiConverter $taskConverter,
        private readonly TaskManager $tasks,
    ) {}

    #[Route(pattern: '~^/api/event/play/(?<id>[^/]+)/$~')]
    public function execute(string $id): array
    {
        $user = $this->authorization->getNullableAuthorizedUser();

        if (null === $user) {
            throw new NotAuthorizedError();
        }

        $eventIdentifier = filter_var($id, FILTER_VALIDATE_INT);

        if (!\is_int($eventIdentifier)) {
            throw new NotFoundError();
        }

        $event = $this->events->findEventsByBitrixId(
            active: true,
            bitrixId: $eventIdentifier,
        );

        if (null === $event) {
            throw new NotFoundError();
        }

        $participant = $this->participants->findFirstElement([
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
            'PROPERTY_EVENT' => $event->id,
            'PROPERTY_USER' => $user->userIdentifier,
        ]);

        if (null === $participant) {
            throw new NotAccessibleError();
        }

        $taskDataList = [];

        $taskList = $this->tasks->findAllElements([
            'ACTIVE' => 'Y',
            'PROPERTY_EVENT' => $event->id,
        ]);

        $this->statuses->loadTaskStatus($taskList, $user);

        foreach ($taskList as $task) {
            $taskDataList[] = $this->taskConverter->convertTaskToApi($task, $user);
        }

        return [
            'tasks' => $taskDataList,
        ];
    }
}
