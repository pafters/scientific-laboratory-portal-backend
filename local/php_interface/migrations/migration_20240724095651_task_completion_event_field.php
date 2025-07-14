<?php

declare(strict_types=1);

use Phosagro\Migration\IblockPropertyHelper;

return static function (IblockPropertyHelper $properties): void {
    $properties->createPropertyElement('event', 'Completion', 'EVENT', 'Событие', 'content', 'Event', true, [
        'SORT' => 5,
    ]);
};
