<?php

declare(strict_types=1);

namespace Phosagro\Event\Listeners;

use Bitrix\Main\EventManager;
use Phosagro\Iblocks;
use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\Manager\Errors\FoundMultipleException;
use Phosagro\Manager\EventManager as ManagerEventManager;
use Phosagro\Manager\ParticipantManager;
use Phosagro\Object\Bitrix\User;
use Phosagro\Object\Event;
use Phosagro\System\Array\Accessor;
use Phosagro\System\Array\AccessorException;
use Phosagro\System\Array\MissingRequiredException;
use Phosagro\System\Array\WrongTypeException;
use Phosagro\System\Iblock\Properties;
use Phosagro\System\ListenerInterface;

final class FillParticipantNameAndCode implements ListenerInterface
{
    public function __construct(
        private readonly \CMain $bitrix,
        private readonly ManagerEventManager $events,
        private readonly ParticipantManager $participants,
        private readonly Properties $properties,
        private readonly UserManager $users,
    ) {}

    public function registerListeners(EventManager $eventManager): void
    {
        $eventManager->addEventHandler('iblock', 'OnBeforeIBlockElementAdd', $this->executeBeforeCreate(...));
        $eventManager->addEventHandler('iblock', 'OnBeforeIBlockElementUpdate', $this->executeBeforeUpdate(...));
    }

    private function executeBeforeCreate(array &$fields): bool
    {
        $iblockId = (int) ($fields['IBLOCK_ID'] ?? 0);

        if ($iblockId !== Iblocks::participantId()) {
            return true;
        }

        try {
            $accessor = new Accessor($fields);
            $eventId = $this->fetchSingleIntValue($this->fetchEventValues($accessor, $iblockId));
            $userId = $this->fetchSingleIntValue($this->fetchUserValues($accessor, $iblockId));
        } catch (AccessorException $error) {
            $this->bitrix->ThrowException($error->getMessage());

            return false;
        }

        return $this->updateNameAndCode($fields, $eventId, $userId);
    }

    private function executeBeforeUpdate(array &$fields): bool
    {
        $iblockId = (int) ($fields['IBLOCK_ID'] ?? 0);

        if ($iblockId !== Iblocks::participantId()) {
            return true;
        }

        $participantId = (int) ($fields['ID'] ?? 0);

        if ($participantId <= 0) {
            return true;
        }

        try {
            $participant = $this->participants->findSingleElement(['ID' => $participantId]);
        } catch (FoundMultipleException $error) {
            $this->bitrix->ThrowException($error->getMessage());

            return false;
        }

        if (null === $participant) {
            return true;
        }

        $changed = false;

        $accessor = new Accessor($fields);

        try {
            $eventId = $this->fetchSingleIntValue($this->fetchEventValues($accessor, $iblockId));
        } catch (AccessorException) {
            $eventId = null;
        }

        if ((null !== $eventId) && ($eventId !== $participant->eventIdentifier)) {
            $changed = true;
        }

        try {
            $userId = $this->fetchSingleIntValue($this->fetchUserValues($accessor, $iblockId));
        } catch (AccessorException) {
            $userId = null;
        }

        if ((null !== $userId) && ($userId !== $participant->userIdentifier)) {
            $changed = true;
        }

        if (!$changed) {
            return true;
        }

        if (null === $eventId) {
            $eventId = $participant->eventIdentifier;
        }

        if (null === $userId) {
            $userId = $participant->userIdentifier;
        }

        return $this->updateNameAndCode($fields, $eventId, $userId);
    }

    private function fetchEventValues(Accessor $accessor, int $iblockId): Accessor
    {
        $properties = $accessor->getObject('PROPERTY_VALUES');

        try {
            return $properties->getObject('EVENT');
        } catch (MissingRequiredException) {
            return $properties->getObject($this->properties->getPropertyId($iblockId, 'EVENT'));
        }
    }

    private function fetchSingleIntValue(Accessor $accessor): ?int
    {
        $keys = $accessor->getKeys();

        if ([] === $keys) {
            return null;
        }

        $firstKey = $keys[array_key_first($keys)];
        $accessor->assertKeys([$firstKey]);
        $values = $accessor->getObject($firstKey);

        try {
            return $values->getInt('VALUE');
        } catch (WrongTypeException) {
            return $values->getIntParsed('VALUE');
        }
    }

    private function fetchUserValues(Accessor $accessor, int $iblockId): Accessor
    {
        $properties = $accessor->getObject('PROPERTY_VALUES');

        try {
            return $properties->getObject('USER');
        } catch (MissingRequiredException) {
            return $properties->getObject($this->properties->getPropertyId($iblockId, 'USER'));
        }
    }

    private function generateCode(Event $event, User $user): string
    {
        return sprintf('%s-%s', $event->id, $this->users->getId($user));
    }

    private function generateName(Event $event, User $user): string
    {
        return sprintf('%s - %s', $event->name, $user->login);
    }

    private function updateNameAndCode(array &$fields, int $eventId, int $userId): bool
    {
        $event = $this->events->findEventsByBitrixId($eventId);

        if (null === $event) {
            $this->bitrix->ThrowException(GetMessage('EVENT_NOT_FOUND'));

            return false;
        }

        $user = $this->users->findById($userId);

        if (null === $user) {
            $this->bitrix->ThrowException(GetMessage('USER_NOT_FOUND'));

            return false;
        }

        $fields['NAME'] = $this->generateName($event, $user);
        $fields['CODE'] = $this->generateCode($event, $user);

        return true;
    }
}
