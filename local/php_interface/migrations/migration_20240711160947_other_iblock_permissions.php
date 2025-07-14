<?php

declare(strict_types=1);

use Phosagro\Migration\IblockPermissionHelper;

/*
 * В миграции migration_20240711153929_group_identifiers.php группы были пересозданы,
 * поэтому нужно заново применить все связанные с группами миграции.
 */

return static function (IblockPermissionHelper $permissions): void {
    $permissions->setIblockPermissons('content', 'Faq', [
        'education_moderator' => 'R',
        'event_moderator' => 'R',
        'guest' => 'R',
        'public_moderator' => 'W',
        'technical_administrator' => 'X',
    ]);

    $permissions->setIblockPermissons('voting', 'Voting', [
        'education_moderator' => 'W',
        'event_moderator' => 'R',
        'guest' => 'R',
        'public_moderator' => 'R',
        'technical_administrator' => 'X',
    ]);
};
