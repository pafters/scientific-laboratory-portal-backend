<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Event\Participatability\Errors\ParticipatabilityException;
use Phosagro\Event\Participatability\Participator;
use Phosagro\Manager\EventManager;
use Phosagro\System\Api\Errors\NotAuthorizedError;
use Phosagro\System\Api\Errors\NotFoundError;
use Phosagro\System\Api\Errors\NotParticipatableError;
use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;

final class EventParticipate
{
    public function __construct(
        private readonly AuthorizationContext $authorization,
        private readonly EventManager $events,
        private readonly Participator $participator,
    ) {}

    #[Route(method: 'POST', pattern: '~^/api/event/participate/(?<eventId>[^/]+)/$~')]
    public function execute(string $eventId): array
    {
        $eventIdentifier = filter_var($eventId, FILTER_VALIDATE_INT);

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

        $user = $this->authorization->getNullableAuthorizedUser();

        if (null === $user) {
            throw new NotAuthorizedError();
        }

        try {
            $participant = $this->participator->participate($event, $user);
        } catch (ParticipatabilityException $error) {
            throw new NotParticipatableError($error);
        }

        return $participant->toApi();
    }
}
