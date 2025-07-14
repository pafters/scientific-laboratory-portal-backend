<?php

declare(strict_types=1);

namespace Phosagro\Event\Participatability;

use Phosagro\Enum\UserField;
use Phosagro\Event\Participatability\Errors\AgeIsMismatchedException;
use Phosagro\Event\Participatability\Errors\AgeIsNotCalculatedException;
use Phosagro\Event\Participatability\Errors\EventIsArchivedException;
use Phosagro\Event\Participatability\Errors\EventIsCompletedException;
use Phosagro\Event\Participatability\Errors\EventIsNotPublishedException;
use Phosagro\Event\Participatability\Errors\EventIsRunningException;
use Phosagro\Event\Participatability\Errors\GroupIsMismatchedException;
use Phosagro\Event\Participatability\Errors\ParticipatabilityException;
use Phosagro\Event\Participatability\Errors\UserIsAlreadyParticipantException;
use Phosagro\Event\Participatability\Errors\UserIsBlockedException;
use Phosagro\Event\Participatability\Errors\UserIsNotConfirmedEmailException;
use Phosagro\Event\Participatability\Errors\UserIsNotConfirmedPhoneException;
use Phosagro\Event\Participatability\Errors\UserIsNotFilledProfileException;
use Phosagro\Event\Participatability\Errors\UserIsNotModeratedException;
use Phosagro\Event\Participatability\Errors\UserRefusedToParticipateException;
use Phosagro\Manager\GroupManager;
use Phosagro\Manager\ParticipantManager;
use Phosagro\Object\Bitrix\User;
use Phosagro\Object\Event;
use Phosagro\Object\Participant;
use Phosagro\System\Clock;

final class ParticipatabilityChecker
{
    /** @var \WeakMap<Event,\WeakMap<User,false|ParticipatableException>> */
    private \WeakMap $participability;

    public function __construct(
        private readonly Clock $clock,
        private readonly GroupManager $groups,
        private readonly ParticipantManager $participants,
    ) {
        $this->participability = new \WeakMap();
    }

    /**
     * @throws ParticipatabilityException
     */
    public function assertParticipatable(Event $event, User $user): void
    {
        $error = $this->participability[$event][$user] ?? null;

        if (null === $error) {
            throw new \LogicException('Participatability is not loaded.');
        }

        if (false !== $error) {
            throw $error;
        }
    }

    public function isParticipatable(Event $event, User $user): bool
    {
        try {
            $this->assertParticipatable($event, $user);
        } catch (ParticipatabilityException) {
            return false;
        }

        return true;
    }

    /**
     * @param Event|Event[] $eventList
     * @param User|User[]   $userList
     */
    public function loadPrticipatability(array|Event $eventList, array|User $userList): void
    {
        $now = $this->clock->now();

        if ($eventList instanceof Event) {
            $eventList = [$eventList];
        }

        if ($userList instanceof User) {
            $userList = [$userList];
        }

        $userGroupIndex = $this->loadUserGroups($userList);
        $participationIndex = $this->loadParticipation($eventList, $userList);

        foreach ($eventList as $event) {
            $this->participability[$event] ??= new \WeakMap();
            $p = $this->participability[$event];
            foreach ($userList as $user) {
                // == (Не из ТЗ) Пользователь уже принимает участие - значит кнопка не должна показываться == //

                $participant = $participationIndex[$event->id][$user->userIdentifier] ?? null;

                if ((null !== $participant) && $participant->refused) {
                    $p[$user] = new UserRefusedToParticipateException($event, $user);

                    continue;
                }

                if (null !== $participant) {
                    $p[$user] = new UserIsAlreadyParticipantException($event, $user);

                    continue;
                }

                // == (ТЗ 6.2.1.2) запись на событие открыта == //

                if (!$event->active) {
                    $p[$user] = new EventIsNotPublishedException($event, $user);

                    continue;
                }

                if ($event->archived) {
                    $p[$user] = new EventIsArchivedException($event, $user);

                    continue;
                }

                if ((null !== $event->endAt) && ($event->endAt <= $now)) {
                    $p[$user] = new EventIsCompletedException($event, $user);

                    continue;
                }

                $applicationEndsAt = $event->getActualApplicationEndsAt();

                if ((null !== $applicationEndsAt) && ($applicationEndsAt <= $now)) {
                    $p[$user] = new EventIsRunningException($event, $user);

                    continue;
                }

                if ((null !== $event->startAt) && ($event->startAt <= $now)) {
                    $p[$user] = new EventIsRunningException($event, $user);

                    continue;
                }

                // == (ТЗ 6.2.1.2) учетная запись пользователя активирована и подтверждена == //

                if (!$user->emailIsConfirmed) {
                    $p[$user] = new UserIsNotConfirmedEmailException($event, $user);

                    continue;
                }

                if (!$user->phoneIsConfirmed) {
                    $p[$user] = new UserIsNotConfirmedPhoneException($event, $user);

                    continue;
                }

                if ($user->blocked) {
                    $p[$user] = new UserIsBlockedException($event, $user);

                    continue;
                }

                if (!$user->active) {
                    $p[$user] = new UserIsNotModeratedException($event, $user);

                    continue;
                }

                // == (ТЗ 6.2.1.2) авторизованный пользователь имеет заполненный необходимыми данными профиль == //

                if (null === $user->birthday) {
                    $p[$user] = new UserIsNotFilledProfileException($event, $user, UserField::BIRTHDATE);

                    continue;
                }

                if (null === $user->city) {
                    $p[$user] = new UserIsNotFilledProfileException($event, $user, UserField::CITY);

                    continue;
                }

                if ('' === $user->email) {
                    $p[$user] = new UserIsNotFilledProfileException($event, $user, UserField::EMAIL);

                    continue;
                }

                if ('' === $user->login) {
                    $p[$user] = new UserIsNotFilledProfileException($event, $user, UserField::LOGIN);

                    continue;
                }

                if ('' === $user->name) {
                    $p[$user] = new UserIsNotFilledProfileException($event, $user, UserField::NAME);

                    continue;
                }

                if ('' === $user->phone) {
                    $p[$user] = new UserIsNotFilledProfileException($event, $user, UserField::PHONE);

                    continue;
                }

                if ('' === $user->surname) {
                    $p[$user] = new UserIsNotFilledProfileException($event, $user, UserField::SURNAME);

                    continue;
                }

                // == (ТЗ 6.2.1.2) возраст пользователя на момент подачи заявки соответствует требованиям события == //

                try {
                    $userAge = $user->calculateAge($now);
                } catch (\RuntimeException) {
                    $p[$user] = new AgeIsNotCalculatedException($event, $user);

                    continue;
                }

                if ($userAge < $event->ageCategory->minimalAge) {
                    $p[$user] = new AgeIsMismatchedException($event, $user);

                    continue;
                }

                if (($event->ageCategory?->maximalAge ?? PHP_INT_MAX) < $userAge) {
                    $p[$user] = new AgeIsMismatchedException($event, $user);

                    continue;
                }

                // == (Не из ТЗ) не реализована проверка на группу пользователя для участия в событии? == //

                $eventGroups = $event->participantGroupIdentifiers;
                $userGroups = $userGroupIndex[$user->userIdentifier] ?? [];

                if (([] !== $eventGroups) && ([] === array_intersect($eventGroups, $userGroups))) {
                    $p[$user] = new GroupIsMismatchedException($event, $user);

                    continue;
                }

                $p[$user] = false;
            }
        }
    }

    /**
     * @param Event[] $eventList
     * @param User[]  $userList
     *
     * @return array<int,array<int,Participant>>
     */
    private function loadParticipation(array $eventList, array $userList): array
    {
        /** @var array<int,array<int,bool>> */
        $result = [];

        if (([] !== $eventList) && ([] !== $userList)) {
            $found = $this->participants->findAllElements([
                'PROPERTY_EVENT' => array_values(array_map(
                    static fn (Event $event): int => $event->id,
                    $eventList,
                )),
                'PROPERTY_USER' => array_values(array_map(
                    static fn (User $user): int => $user->userIdentifier,
                    $userList,
                )),
            ]);

            foreach ($found as $participant) {
                $result[$participant->eventIdentifier] ??= [];
                $result[$participant->eventIdentifier][$participant->userIdentifier] = $participant;
            }
        }

        return $result;
    }

    /**
     * @param User[] $userList
     *
     * @return array<int,int[]>
     */
    private function loadUserGroups(array $userList): array
    {
        return array_map(
            fn (array $groupList): array => array_map(
                $this->groups->findBitrixId(...),
                $groupList,
            ),
            $this->groups->findGroupsForUserList(
                array_map(
                    static fn (User $user): int => $user->userIdentifier,
                    $userList,
                ),
            ),
        );
    }
}
