<?php

declare(strict_types=1);

namespace Phosagro\Event\Listeners;

use Bitrix\Main\EventManager;
use Phosagro\Iblocks;
use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\Manager\EventManager as ManagerEventManager;
use Phosagro\Manager\ParticipantManager;
use Phosagro\System\Iblock\Properties;
use Phosagro\System\ListenerInterface;

final class SendParticipationRequestEmails implements ListenerInterface
{
    public function __construct(
        private readonly ManagerEventManager $events,
        private readonly ParticipantManager $participants,
        private readonly Properties $properties,
        private readonly UserManager $users,
    ) {}

    public function registerListeners(EventManager $eventManager): void
    {
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementUpdate', $this->executeAfterUpdate(...));
    }

    private function executeAfterUpdate(array $fields): void
    {
        $iblockId = (int) ($fields['IBLOCK_ID'] ?? 0);

        if ($iblockId !== Iblocks::participantId()) {
            return;
        }

        $participantId = (int) ($fields['ID'] ?? 0);

        if (0 === $participantId) {
            return;
        }

        $participant = $this->participants->findFirstElement(['ID' => $participantId]);

        if (null === $participant) {
            return;
        }

        $user = $this->users->findById($participant->userIdentifier);

        if (null === $user) {
            return;
        }

        $event = $this->events->findEventsByBitrixId($participant->eventIdentifier);

        if ($participant->rejected) {
            if (!$participant->preventRejectionEmail) {
                $this->participants->preventRejectionEmail($participant);

                \CEvent::Send('EVENT_PARTICIPATION_REJECT', 's1', [
                    'EVENT_NAME' => (null === $event) ? '<DELETED>' : $event->name,
                    'REASON' => $participant->rejectionReason,
                    'USER_EMAIL' => $user->email,
                ]);
            }
        } elseif ($participant->confirmed) {
            if (!$participant->preventConfirmationEmail) {
                $this->participants->preventConfirmationEmail($participant);

                \CEvent::Send('EVENT_PARTICIPATION_CONFIRM', 's1', [
                    'EVENT_NAME' => (null === $event) ? '<DELETED>' : $event->name,
                    'USER_EMAIL' => $user->email,
                ]);
            }
        }
    }
}
