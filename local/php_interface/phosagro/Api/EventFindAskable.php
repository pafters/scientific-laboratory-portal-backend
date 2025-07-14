<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Converter\EventForSelectToApiConverter;
use Phosagro\Manager\EventManager;
use Phosagro\Manager\ParticipantManager;
use Phosagro\Object\Event;
use Phosagro\Object\Participant;
use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;
use Phosagro\Util\Collection;

final class EventFindAskable
{
    public function __construct(
        private readonly AuthorizationContext $authorization,
        private readonly EventForSelectToApiConverter $converter,
        private readonly EventManager $events,
        private readonly ParticipantManager $participants,
    ) {}

    #[Route(pattern: '~^/api/event/find\-askable/$~')]
    public function execute(): array
    {
        $eventList = [];

        $user = $this->authorization->getNullableAuthorizedUser();

        if (null !== $user) {
            $participantList = $this->participants->getConfirmedUserParticipantsIndex($user)[$user];

            $getEventIdentifier = static fn (Participant $participant): int => $participant->eventIdentifier;
            $eventIdentifierList = array_map($getEventIdentifier, $participantList);
            $eventIdentifierList = Collection::identifierList($eventIdentifierList);

            if ([] !== $eventIdentifierList) {
                $eventList = $this->events->findAllElements([
                    'ACTIVE' => 'Y',
                    'ACTIVE_DATE' => 'Y',
                    'ID' => $eventIdentifierList,
                ]);
            }
        }

        $dataIndex = $this->converter->convertEventForSelectToApi($eventList);

        return [
            'events' => array_map(static fn (Event $e): array => $dataIndex[$e], $eventList),
        ];
    }
}
