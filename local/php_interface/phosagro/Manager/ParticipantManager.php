<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\Iblocks;
use Phosagro\Manager\Errors\NotFoundException;
use Phosagro\Object\Bitrix\User;
use Phosagro\Object\Event;
use Phosagro\Object\Participant;
use Phosagro\System\Iblock\Properties;
use Phosagro\Util\Collection;

/**
 * @extends AbstractIblockElementManager<Participant>
 */
final class ParticipantManager extends AbstractIblockElementManager
{
    public function __construct(
        private readonly Properties $properties,
    ) {}

    public function deleteParticipant(Participant $participant): void
    {
        $this->deleteByIdentifier($participant->participantIdentifier);
    }

    public function getConfirmedParticipant(
        Event $event,
        User $user,
    ): Participant {
        return $this->getParticipant($event, $user, $this->getConfirmedParticipantFilter());
    }

    /**
     * @param Event|Event[] $eventList
     * @param User|User[]   $userList
     *
     * @return \WeakMap<Event,\WeakMap<User,Participant>>
     */
    public function getConfirmedParticipantIndex(
        array|Event $eventList,
        array|User $userList,
        array $filter = [],
    ): \WeakMap {
        return $this->getParticipantIndex($eventList, $userList, $this->getConfirmedParticipantFilter() + $filter);
    }

    /**
     * @return Participant[]
     */
    public function getConfirmedUserParticipants(
        User $user,
        array $filter = [],
    ): array {
        return $this->getUserParticipants($user, $this->getConfirmedParticipantFilter() + $filter);
    }

    /**
     * @param User|User[] $userList
     *
     * @return \WeakMap<User,Participant[]>
     */
    public function getConfirmedUserParticipantsIndex(
        array|User $userList,
        array $filter = [],
    ): \WeakMap {
        return $this->getUserParticipantsIndex($userList, $this->getConfirmedParticipantFilter() + $filter);
    }

    public function getParticipant(
        Event $event,
        User $user,
        array $filter = [],
    ): Participant {
        $found = $this->getParticipantIndex($event, $user, $filter)[$event][$user] ?? null;

        if (null === $found) {
            throw new NotFoundException(Participant::class);
        }

        return $found;
    }

    /**
     * @param Event|Event[] $eventList
     * @param User|User[]   $userList
     *
     * @return \WeakMap<Event,\WeakMap<User,Participant>>
     */
    public function getParticipantIndex(
        array|Event $eventList,
        array|User $userList,
        array $filter = [],
    ): \WeakMap {
        /** @var \WeakMap<Event,\WeakMap<User,Participant>> $result */
        $result = new \WeakMap();

        if ($eventList instanceof Event) {
            $eventList = [$eventList];
        }

        if ($userList instanceof User) {
            $userList = [$userList];
        }

        /** @var array<int,array<int,Participant>> $index */
        $index = [];

        if (([] !== $eventList) && ([] !== $userList)) {
            $found = $this->findAllElements([
                'PROPERTY_EVENT' => Collection::identifierList(array_map(
                    static fn (Event $event): int => $event->id,
                    $eventList,
                )),
                'PROPERTY_USER' => Collection::identifierList(array_map(
                    static fn (User $user): int => $user->userIdentifier,
                    $userList,
                )),
            ] + $filter);

            foreach ($found as $participant) {
                $index[$participant->eventIdentifier] ??= [];
                $index[$participant->eventIdentifier][$participant->userIdentifier] = $participant;
            }
        }

        foreach ($eventList as $event) {
            $result[$event] ??= new \WeakMap();
            foreach ($userList as $user) {
                $participant = $index[$event->id][$user->userIdentifier] ?? null;
                if (null !== $participant) {
                    $result[$event][$user] = $participant;
                }
            }
        }

        return $result;
    }

    /**
     * @return Participant[]
     */
    public function getUserParticipants(
        User $user,
        array $filter = [],
    ): array {
        return $this->getUserParticipantsIndex($user, $filter)[$user];
    }

    /**
     * @param User|User[] $userList
     *
     * @return \WeakMap<User,Participant[]>
     */
    public function getUserParticipantsIndex(
        array|User $userList,
        array $filter = [],
    ): \WeakMap {
        /** @var \WeakMap<User,Participant[]> $result */
        $result = new \WeakMap();

        if ($userList instanceof User) {
            $userList = [$userList];
        }

        /** @var array<int,Participant[]> $index */
        $index = [];

        if ([] !== $userList) {
            $found = $this->findAllElements([
                'PROPERTY_USER' => Collection::identifierList(array_map(
                    static fn (User $user): int => $user->userIdentifier,
                    $userList,
                )),
            ] + $filter);

            foreach ($found as $participant) {
                $index[$participant->userIdentifier] ??= [];
                $index[$participant->userIdentifier][] = $participant;
            }
        }

        foreach ($userList as $user) {
            $result[$user] = $index[$user->userIdentifier] ?? [];
        }

        return $result;
    }

    public function preventConfirmationEmail(Participant $participant): void
    {
        $manager = new \CIBlockElement();

        $yes = $this->properties->getEnumId(Iblocks::participantId(), 'PREVENT_CONFIRM_EMAIL', 'Y');

        $manager->SetPropertyValuesEx(
            $participant->participantIdentifier,
            Iblocks::participantId(),
            [
                'PREVENT_CONFIRM_EMAIL' => $yes,
            ],
        );
    }

    public function preventRejectionEmail(Participant $participant): void
    {
        $manager = new \CIBlockElement();

        $yes = $this->properties->getEnumId(Iblocks::participantId(), 'PREVENT_REJECT_EMAIL', 'Y');

        $manager->SetPropertyValuesEx(
            $participant->participantIdentifier,
            Iblocks::participantId(),
            [
                'PREVENT_REJECT_EMAIL' => $yes,
            ],
        );
    }

    public function refuseParticipant(Participant $participant): void
    {
        \CIBlockElement::SetPropertyValuesEx($participant->participantIdentifier, $this->getIblockId(), [
            'REFUSED' => $this->properties->getEnumId($this->getIblockId(), 'REFUSED', 'Y'),
        ]);
    }

    protected function createFromBitrixData(array $row): object
    {
        return new Participant(
            trim((string) $row['CODE']),
            trim((string) $row['NAME']),
            '' !== trim((string) $row['PROPERTY_CAME_VALUE']),
            'Y' === trim((string) $row['ACTIVE']),
            (int) $row['PROPERTY_EVENT_VALUE'],
            (int) $row['ID'],
            '' !== trim((string) $row['PROPERTY_PREVENT_CONFIRM_EMAIL_VALUE']),
            '' !== trim((string) $row['PROPERTY_PREVENT_REJECT_EMAIL_VALUE']),
            '' !== trim((string) $row['PROPERTY_REFUSED_VALUE']),
            trim((string) $row['PROPERTY_REJECTION_VALUE']),
            (int) $row['PROPERTY_USER_VALUE'],
        );
    }

    protected function getBitrixFields(): array
    {
        return [
            'ACTIVE',
            'CODE',
            'ID',
            'NAME',
            'PROPERTY_CAME',
            'PROPERTY_EVENT',
            'PROPERTY_PREVENT_CONFIRM_EMAIL',
            'PROPERTY_PREVENT_REJECT_EMAIL',
            'PROPERTY_REFUSED',
            'PROPERTY_REJECTION',
            'PROPERTY_USER',
        ];
    }

    private function getConfirmedParticipantFilter(): array
    {
        return [
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
        ];
    }
}
