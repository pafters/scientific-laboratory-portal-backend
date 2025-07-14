<?php

declare(strict_types=1);

use Phosagro\Migration\IblockPropertyHelper;

return static function (IblockPropertyHelper $propertyHelper): void {
    $propertyHelper->createPropertyUser('directory', 'UserGroup', 'USERS', 'Пользователи', false, [
        'MULTIPLE' => 'Y',
        'MULTIPLE_CNT' => 1,
    ]);
};
