<?php

declare(strict_types=1);

namespace Phosagro\Event\Participatability;

use Phosagro\Iblocks;
use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\Manager\ParticipantManager;
use Phosagro\Object\Bitrix\User;
use Phosagro\Object\Event;
use Phosagro\Object\Participant;
use Phosagro\System\UrlManager;

final class Participator
{
    public function __construct(
        private readonly ParticipantManager $participants,
        private readonly ParticipatabilityChecker $participatability,
        private readonly UrlManager $urls,
        private readonly UserManager $users,
    ) {}

    public function participate(Event $event, User $user): Participant
    {
        $this->participatability->loadPrticipatability($event, $user);
        $this->participatability->assertParticipatable($event, $user);

        $moderator = $this->users->findById($event->moderatorIdentifier);

        if (null === $moderator) {
            throw new \RuntimeException(sprintf('No moderator in the event "%s".', $event->id));
        }

        $manager = new \CIBlockElement();

        $result = $manager->Add([
            'ACTIVE' => 'N',
            'CODE' => $this->generateCode($event, $user),
            'NAME' => $this->generateName($event, $user),
            'IBLOCK_ID' => Iblocks::participantId(),
            'PROPERTY_VALUES' => [
                'EVENT' => [
                    'n0' => [
                        'VALUE' => $event->id,
                    ],
                ],
                'USER' => [
                    'n0' => [
                        'VALUE' => $this->users->getId($user),
                    ],
                ],
            ],
        ]);

        if (!$result) {
            throw new \RuntimeException($manager->LAST_ERROR);
        }

        $participant = $this->participants->getSingleElement(['ID' => (int) $result]);

        \CEvent::Send('EVENT_PARTICIPATION_REQUEST', 's1', [
            'ADMIN_URL' => $this->urls->makeAbsolute('/bitrix/admin/iblock_element_edit.php?'.http_build_query([
                'IBLOCK_ID' => sprintf('%d', Iblocks::participantId()),
                'ID' => sprintf('%d', $participant->participantIdentifier),
                'WF' => 'Y',
                'find_section_section' => '-1',
                'lang' => 'ru',
                'type' => 'event',
            ])),
            'EVENT_NAME' => $event->name,
            'MODERATOR_EMAIL' => $moderator->email,
            'USER_EMAIL' => $user->email,
            'USER_LOGIN' => $user->login,
        ]);

        return $participant;
    }

    private function generateCode(Event $event, User $user): string
    {
        return sprintf('%s-%s', $event->id, $this->users->getId($user));
    }

    private function generateName(Event $event, User $user): string
    {
        return sprintf('%s - %s', $event->name, $user->login);
    }
}
