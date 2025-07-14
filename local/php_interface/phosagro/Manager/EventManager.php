<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\Iblocks;
use Phosagro\Manager\Errors\NotFoundException;
use Phosagro\Object\Bitrix\User;
use Phosagro\Object\Event;
use Phosagro\Object\Group;
use Phosagro\System\Iblock\Properties;
use Phosagro\Util\Collection;
use Phosagro\Util\Date;
use Phosagro\Util\DateFormat;
use Phosagro\Util\Text;

/**
 * @extends AbstractIblockElementManager<Event>
 */
final class EventManager extends AbstractIblockElementManager
{
    public function __construct(
        private readonly AgeCategoryManager $ageCategoryManager,
        private readonly CityManager $cityManager,
        private readonly GroupManager $groupManager,
        private readonly Properties $properties,
    ) {}

    /**
     * @param null|array<int,Group> $groupList
     */
    public function findEventsByBitrixId(
        int $bitrixId,
        ?bool $active = null,
        ?array $groupList = null,
    ): ?Event {
        $filter = [
            'ID' => $bitrixId,
        ];

        if (null !== $active) {
            if ($active) {
                $filter['ACTIVE'] = 'Y';
                $filter['ACTIVE_DATE'] = 'Y';
            } else {
                $filter[] = [
                    'LOGIC' => 'OR',
                    '!ACTIVE' => 'Y',
                    '!ACTIVE_DATE' => 'Y',
                ];
            }
        }

        if (null !== $groupList) {
            $groupIdIndex = [];
            foreach ($groupList as $group) {
                $groupId = $this->groupManager->findBitrixId($group);
                if (null !== $groupId) {
                    $groupIdIndex[$groupId] = null;
                }
            }
            $groupIdList = array_keys($groupIdIndex);
            sort($groupIdList, SORT_NUMERIC);
            $filter[] = [
                'LOGIC' => 'OR',
                [
                    'PROPERTY_DISPLAY_GROUPS' => false,
                ],
                [
                    'PROPERTY_DISPLAY_GROUPS' => $groupIdList,
                ],
            ];
        }

        return $this->findFirstElement($filter);
    }

    public function getEventByIdentifier(int $eventIdentifier): Event
    {
        return $this->findEventsByBitrixId($eventIdentifier) ?? throw new NotFoundException('Event');
    }

    /**
     * @param int[] $identifierList
     *
     * @return array<int,Event>
     */
    public function getEventsByIdentifiers(array $identifierList): array
    {
        /** @var array<int,Event> $index */
        $index = [];

        if ([] !== $identifierList) {
            $eventList = $this->findAllElements([
                'ID' => Collection::identifierList($identifierList),
            ]);

            foreach ($eventList as $event) {
                $index[$event->id] = $event;
            }
        }

        return $index;
    }

    /**
     * @return Event[]
     */
    public function getEventsForSelect(?User $user): array
    {
        $filter = [
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
        ];

        if (null !== $user) {
            $filter[] = [
                'LOGIC' => 'OR',
                [
                    'PROPERTY_DISPLAY_GROUPS' => false,
                ],
                [
                    'PROPERTY_DISPLAY_GROUPS' => array_map(
                        static fn (Group $g): int => $g->groupIdentifier,
                        $this->groupManager->findGroupsForUser($user->userIdentifier)
                    ),
                ],
            ];
        } else {
            $filter['PROPERTY_DISPLAY_GROUPS'] = false;
        }

        return $this->findAllElements($filter);
    }

    public function preventRegistrationCloseEmail(Event $event): void
    {
        $manager = new \CIBlockElement();

        $yes = $this->properties->getEnumId(Iblocks::eventId(), 'PREVENT_REGISTRATION_CLOSE_EMAIL', 'Y');

        $manager->SetPropertyValuesEx(
            $event->id,
            Iblocks::eventId(),
            [
                'PREVENT_REGISTRATION_CLOSE_EMAIL' => $yes,
            ],
        );
    }

    protected function createFromBitrixData(array $row): object
    {
        return new Event(
            'Y' === trim((string) $row['ACTIVE']),
            $this->parseBitrixDate($row['ACTIVE_FROM']),
            $this->parseBitrixDate($row['ACTIVE_TO']),
            $this->ageCategoryManager->findOne((int) $row['PROPERTY_AGE_CATEGORY_VALUE']),
            $this->parseBitrixDate($row['PROPERTY_PARTICIPATION_ENDS_AT_VALUE']),
            null !== $row['PROPERTY_ARCHIVED_ENUM_ID'],
            $this->cityManager->findOne((int) $row['PROPERTY_CITY_VALUE']),
            (null === $row['DETAIL_PICTURE']) ? null : (int) $row['DETAIL_PICTURE'],
            $this->parseBitrixDate($row['PROPERTY_ENDS_AT_VALUE']),
            trim((string) $row['PROPERTY_FOR_VALUE']),
            Text::bitrix(trim((string) $row['DETAIL_TEXT']), trim((string) $row['DETAIL_TEXT_TYPE'])),
            array_map('\intval', $row['PROPERTY_GALLERY_VALUE']),
            (int) $row['ID'],
            (int) $row['PROPERTY_MODERATOR_VALUE'],
            $row['NAME'],
            array_map('\intval', $row['PROPERTY_PARTICIPANT_GROUPS_VALUE']),
            array_map('\intval', $row['PROPERTY_PARTNERS_VALUE']),
            (int) $row['PROPERTY_POINTS_VALUE'],
            (null === $row['PREVIEW_PICTURE']) ? null : (int) $row['PREVIEW_PICTURE'],
            Text::bitrix(trim((string) $row['PREVIEW_TEXT']), trim((string) $row['PREVIEW_TEXT_TYPE'])),
            $this->parseBitrixDate($row['PROPERTY_STARTS_AT_VALUE']),
        );
    }

    protected function getBitrixFields(): array
    {
        return [
            'ACTIVE',
            'ACTIVE_FROM',
            'ACTIVE_TO',
            'DETAIL_PICTURE',
            'DETAIL_TEXT',
            'DETAIL_TEXT_TYPE',
            'ID',
            'NAME',
            'PREVIEW_PICTURE',
            'PREVIEW_TEXT',
            'PREVIEW_TEXT_TYPE',
            'PROPERTY_AGE_CATEGORY',
            'PROPERTY_ARCHIVED',
            'PROPERTY_CITY',
            'PROPERTY_ENDS_AT',
            'PROPERTY_FOR',
            'PROPERTY_GALLERY',
            'PROPERTY_MODERATOR',
            'PROPERTY_PARTICIPANT_GROUPS',
            'PROPERTY_PARTICIPATION_ENDS_AT',
            'PROPERTY_PARTNERS',
            'PROPERTY_POINTS',
            'PROPERTY_STARTS_AT',
        ];
    }

    protected function getDefaultOrder(): array
    {
        return [
            'property_STARTS_AT' => 'desc',
            'id' => 'desc',
        ];
    }

    private function parseBitrixDate(?string $date): ?\DateTimeImmutable
    {
        if (null === $date) {
            return null;
        }

        return Date::tryFromFormat($date, DateFormat::BITRIX, DateFormat::BITRIX_DATE);
    }
}
