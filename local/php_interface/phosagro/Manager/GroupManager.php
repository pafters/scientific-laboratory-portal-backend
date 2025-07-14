<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\BitrixCache;
use Phosagro\Enum\UserGroupType;
use Phosagro\Iblocks;
use Phosagro\Object\Bitrix\User;
use Phosagro\Object\Group;

/**
 * @method Group[] findAll()
 * @method ?Group  findOne(int $bitrixId)
 */
final class GroupManager extends AbstractDirectory
{
    /** @var array<string,Group[]> */
    private array $byCode = [];

    /**
     * @return Group[]
     */
    public function findByCode(string $code): array
    {
        $this->tryLoadCached();

        return $this->byCode["~{$code}"] ?? [];
    }

    /**
     * @return Group[]
     */
    public function findByType(UserGroupType $type): array
    {
        return $this->findByCode($type->value);
    }

    /**
     * @return array<int,Group>
     */
    public function findGroupsForUser(int $userId): array
    {
        return $this->findGroupsForUserList([$userId])[$userId] ?? [];
    }

    /**
     * @param int[] $userIdList
     *
     * @return array<int,array<int,Group>>
     */
    public function findGroupsForUserList(array $userIdList): array
    {
        /** @var array<int,array<int,Group>> $result */
        $result = [];

        $found = \CIBlockElement::GetList(
            [
                'id' => 'asc',
            ],
            [
                'IBLOCK_ID' => Iblocks::userGroupId(),
                'PROPERTY_USERS' => $userIdList,
            ],
            false,
            false,
            [
                'ID',
                'PROPERTY_USERS',
            ],
        );

        while ($row = $found->Fetch()) {
            $group = $this->findOne((int) $row['ID']);
            $unique = [];
            foreach ($row['PROPERTY_USERS_VALUE'] as $userId) {
                $unique[$userId] = $group;
            }
            foreach ($unique as $userId => $group) {
                $result[$userId][] = $group;
            }
        }

        return $result;
    }

    /**
     * @param Group|Group[] $groupList
     *
     * @return \WeakMap<Group,int[]>
     */
    public function findUserIdentifiersForGroupList(array|Group $groupList): \WeakMap
    {
        /** @var \WeakMap<Group,int[]> */
        $result = new \WeakMap();

        if ($groupList instanceof Group) {
            $groupList = [$groupList];
        }

        $groupIdentifierList = array_map($this->findBitrixId(...), $groupList);
        $groupIdentifierList = array_filter($groupIdentifierList, '\is_object');
        $groupIdentifierList = array_values(array_unique($groupIdentifierList));

        /** @var array<int,int[]> $index */
        $index = [];

        $found = \CIBlockElement::GetList(
            ['id' => 'asc'],
            ['IBLOCK_ID' => Iblocks::userGroupId(), 'ID' => $groupIdentifierList],
            false,
            false,
            ['ID', 'PROPERTY_USERS'],
        );

        while ($row = $found->Fetch()) {
            $userList = array_map('\intval', $row['PROPERTY_USERS_VALUE']);
            $userList = array_values(array_unique($userList));
            $index[(int) $row['ID']] = $userList;
        }

        foreach ($groupList as $group) {
            $groupIdentifier = ($this->findBitrixId($group) ?? 0);
            $result[$group] = ($index[$groupIdentifier] ?? []);
        }

        return $result;
    }

    /**
     * @param User|User[]   $userList
     * @param Group|Group[] $groupList
     */
    public function linkUsers(array|User $userList, array|Group $groupList): void
    {
        if ($userList instanceof User) {
            $userList = [$userList];
        }

        if ($groupList instanceof Group) {
            $groupList = [$groupList];
        }

        $linkingUsers = array_map(static fn (User $u): int => $u->userIdentifier, $userList);
        $linkingUsers = array_values(array_unique($linkingUsers));

        $userIndex = $this->findUserIdentifiersForGroupList($groupList);

        foreach ($groupList as $group) {
            $groupIdentifier = $this->findBitrixId($group);

            if (null === $groupIdentifier) {
                continue;
            }

            $groupUsers = ($userIndex[$group] ?? []);
            $missingUsers = array_diff($linkingUsers, $groupUsers);

            if ([] === $missingUsers) {
                continue;
            }

            $resultUsers = array_merge($groupUsers, $linkingUsers);

            \CIBlockElement::SetPropertyValuesEx($groupIdentifier, Iblocks::userGroupId(), [
                'USERS' => $resultUsers,
            ]);
        }
    }

    protected function createItem(array $row): void
    {
        $group = new Group(
            $row['CODE'],
            (int) $row['ID'],
            $row['NAME'],
            (int) $row['PROPERTY_OWNER_VALUE'],
        );

        $key = "~{$group->groupCode}";

        $this->byCode[$key] ??= [];
        $this->byCode[$key][] = $group;

        $this->addItem($group->groupIdentifier, $group);
    }

    protected function loadDatabase(BitrixCache $cache): array
    {
        /** @var array[] $result */
        $result = [];

        $found = \CIBlockElement::GetList(
            [
                'name' => 'asc',
            ],
            [
                'IBLOCK_ID' => Iblocks::userGroupId(),
            ],
            false,
            false,
            [
                'CODE',
                'ID',
                'NAME',
                'PROPERTY_OWNER',
            ],
        );

        while ($row = $found->Fetch()) {
            $result[] = $row;
        }

        $cache->tag(sprintf('iblock_id_%d', Iblocks::userGroupId()));

        return $result;
    }
}
