<?php

declare(strict_types=1);

namespace Phosagro\Migration;

final class GroupHelper
{
    private readonly \CGroup $manager;

    public function __construct(
        private readonly DatabaseHelper $database,
    ) {
        $this->manager = new \CGroup();
    }

    public function assignGroup(string $group, int $identifier): void
    {
        $this->database->assertSuccess(
            $this->manager->Update($identifier, ['STRING_ID' => $group]),
            'group',
            "{$group}",
            'assign',
            (string) $this->manager->LAST_ERROR,
        );
    }

    public function createGroup(string $group, string $name, array $fields = []): void
    {
        $actualFields = [
            'NAME' => $name,
            'STRING_ID' => $group,
        ] + $fields + [
            'ACTIVE' => 'Y',
            'C_SORT' => $this->getNextSort(),
            'DESCRIPTION' => '',
        ];

        $this->database->assertSuccess(
            $this->manager->Add($actualFields),
            'group',
            "{$group}",
            'create',
            (string) $this->manager->LAST_ERROR,
        );
    }

    public function deleteGroup(string $group): void
    {
        $this->database->assertSuccess(
            $this->manager->Delete($this->getGroupIdentifier($group)),
            'group',
            "{$group}",
            'delete',
            (string) $this->manager->LAST_ERROR,
        );
    }

    public function getGroupIdentifier(string $group): int
    {
        $by = 'id';

        $order = 'asc';

        $found = \CGroup::GetList($by, $order, ['STRING_ID' => $group]);

        $first = $found->Fetch();

        if (!$first) {
            throw new \RuntimeException('Not found group.');
        }

        if ($found->Fetch()) {
            throw new \RuntimeException('Found multiple groups.');
        }

        return (int) $first['ID'];
    }

    public function updateGroup(string $group, array $fields): void
    {
        $this->database->assertSuccess(
            $this->manager->Update($this->getGroupIdentifier($group), $fields),
            'group',
            "{$group}",
            'update',
            (string) $this->manager->LAST_ERROR,
        );
    }

    private function getNextSort(): int
    {
        $by = 'c_sort';

        $order = 'desc';

        $found = \CGroup::GetList($by, $order);

        $first = $found->Fetch();

        if ($first) {
            return ((int) $first['C_SORT']) + 10;
        }

        return 10;
    }
}
