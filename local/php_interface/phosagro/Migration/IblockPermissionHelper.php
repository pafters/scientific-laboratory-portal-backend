<?php

declare(strict_types=1);

namespace Phosagro\Migration;

final class IblockPermissionHelper
{
    public function __construct(
        private readonly GroupHelper $groups,
        private readonly IblockHelper $iblocks,
    ) {}

    public function setIblockPermissons(string $type, string $iblock, array $permissions): void
    {
        $actualPermissions = [];

        foreach ($permissions as $group => $permission) {
            $actualPermissions[$this->groups->getGroupIdentifier($group)] = $permission;
        }

        \CIBlock::SetPermission($this->iblocks->getIblockId($type, $iblock), $actualPermissions);
    }
}
