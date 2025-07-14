<?php

declare(strict_types=1);

/*
 * В миграции migration_20240711153929_group_identifiers.php группы были пересозданы,
 * поэтому нужно заново применить все связанные с группами миграции.
 */

return require __DIR__.DIRECTORY_SEPARATOR.'migration_20240705173009_roles_iblock_permissions.php';
