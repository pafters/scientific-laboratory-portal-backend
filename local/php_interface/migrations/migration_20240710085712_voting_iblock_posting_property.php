<?php

declare(strict_types=1);

use Phosagro\Migration\IblockPropertyHelper;

return static function (IblockPropertyHelper $properties): void {
    $properties->createPropertyNumber('voting', 'Voting', 'POSTING', 'Идентификатор рассылки', false, ['SORT' => 10]);
    $properties->deleteProperty('voting', 'Voting', 'MAILED');
};
