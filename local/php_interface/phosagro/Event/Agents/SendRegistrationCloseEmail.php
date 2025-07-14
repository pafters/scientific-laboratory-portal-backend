<?php

declare(strict_types=1);

namespace Phosagro\Event\Agents;

use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\Manager\EventManager;
use Phosagro\Manager\ParticipantManager;
use Phosagro\System\AgentInterface;
use Phosagro\System\Clock;
use Phosagro\Util\Date;
use Phosagro\Util\DateFormat;

final class SendRegistrationCloseEmail implements AgentInterface
{
    public function __construct(
        private readonly Clock $clock,
        private readonly EventManager $events,
        private readonly ParticipantManager $participants,
        private readonly UserManager $users,
    ) {}

    public function execute(): void
    {
        $now = $this->clock->now();

        $eventList = $this->events->findAllElements(
            [
                'ACTIVE' => 'Y',
                'ACTIVE_DATE' => 'Y',
                'PROPERTY_PREVENT_REGISTRATION_CLOSE_EMAIL' => false,
                [
                    'LOGIC' => 'OR',
                    'ACTIVE_TO' => false,
                    '>ACTIVE_TO' => Date::toFormat($now, DateFormat::BITRIX),
                ],
            ],
            nav: [
                'nTopCount' => 10,
            ],
        );

        foreach ($eventList as $event) {
            $applicationEndsAt = $event->getActualApplicationEndsAt();

            if ((null === $applicationEndsAt) || ($applicationEndsAt > $now)) {
                continue;
            }

            $moderator = $this->users->findById($event->moderatorIdentifier);

            if (null === $moderator) {
                continue;
            }

            $participantListForEmail = (string) GetMessage('NO_PARTICIPANTS');

            /** @var int[] $userIdentifierList */
            $userIdentifierList = [];

            $participantList = $this->participants->findAllElements([
                'ACTIVE' => 'Y',
                'ACTIVE_DATE' => 'Y',
                'PROPERTY_EVENT' => $event->id,
            ]);

            foreach ($participantList as $participant) {
                $userIdentifierList[] = $participant->userIdentifier;
            }

            if ([] !== $userIdentifierList) {
                $participantListForEmail = '';

                $userList = $this->users->findUsers(['=ID' => $userIdentifierList]);

                foreach ($userList as $user) {
                    $participantListForEmail .= sprintf("%s\n", $user->login);
                }
            }

            $this->events->preventRegistrationCloseEmail($event);

            \CEvent::Send('EVENT_REGISTRATION_CLOSE', 's1', [
                'EVENT_NAME' => $event->name,
                'MODERATOR_EMAIL' => $moderator->email,
                'PARTICIPANT_LIST' => $participantListForEmail,
            ]);
        }
    }
}
