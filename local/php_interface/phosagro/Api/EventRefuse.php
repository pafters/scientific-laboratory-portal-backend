<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Event\Participatability\Refuser;
use Phosagro\Manager\EventManager;
use Phosagro\System\Api\Errors\NotAuthorizedError;
use Phosagro\System\Api\Errors\NotFoundError;
use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;

final class EventRefuse
{
    public function __construct(
        private readonly AuthorizationContext $authorization,
        private readonly EventManager $events,
        private readonly Refuser $refuser,
    ) {}

    #[Route(method: 'POST', pattern: '~^/api/event/refuse/(?<eventId>[^/]+)/$~')]
    public function execute(string $eventId): array
    {
        $user = $this->authorization->getNullableAuthorizedUser();

        if (null === $user) {
            throw new NotAuthorizedError();
        }

        $eventIdentifier = filter_var($eventId, FILTER_VALIDATE_INT);

        if (!\is_int($eventIdentifier)) {
            throw new NotFoundError();
        }

        $event = $this->events->findEventsByBitrixId($eventIdentifier);

        if (null === $event) {
            throw new NotFoundError();
        }

        $this->refuser->refuse($event, $user);

        return [];
    }
}
