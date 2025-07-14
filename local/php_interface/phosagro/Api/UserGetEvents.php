<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Converter\UserEventToApiConverter;
use Phosagro\Manager\EventManager;
use Phosagro\Manager\ParticipantManager;
use Phosagro\Object\Participant;
use Phosagro\System\Api\Errors\NotAuthorizedError;
use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;

final class UserGetEvents
{
    public function __construct(
        private readonly AuthorizationContext $authorization,
        private readonly EventManager $events,
        private readonly ParticipantManager $participants,
        private readonly UserEventToApiConverter $userEventsConverter,
    ) {}

    #[Route(pattern: '~^/api/user/get\-events/(?:\?.*)?$~')]
    public function execute(): array
    {
        $user = $this->authorization->getNullableAuthorizedUser();

        if (null === $user) {
            throw new NotAuthorizedError();
        }

        $participantList = $this->participants->findAllElements(['PROPERTY_USER' => $user->userIdentifier]);
        $eventIdentifierList = array_map(static fn (Participant $p): int => $p->eventIdentifier, $participantList);
        $eventIdentifierList = array_values(array_unique($eventIdentifierList));
        sort($eventIdentifierList, SORT_NUMERIC);

        $eventList = ([] === $eventIdentifierList) ? [] : $this->events->findAllElements(
            [
                'ACTIVE' => 'Y',
                'ACTIVE_DATE' => 'Y',
                'ID' => $eventIdentifierList,
            ],
            nav: [
                'bShowAll' => false,
                'checkOutOfRange' => true,
                'iNumPage' => (int) (filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1),
                'nPageSize' => 8,
            ],
        );

        $slim = '1' === filter_input(INPUT_GET, 'slim');

        return [
            'events' => $this->userEventsConverter->convertUserEventsToApi($eventList, $user, $slim),
        ];
    }
}
