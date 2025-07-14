<?php

declare(strict_types=1);

use Phosagro\Migration\IblockPropertyHelper;

 return static function (IblockPropertyHelper $properties): void {
    $properties->deleteProperty('content', 'Event', 'PARTNER');

    $properties->createPropertyElement('content', 'Event', 'PARTNERS', 'Партнёры', 'directory', 'Partner', fields: [
        'MULTIPLE' => 'Y',
        'MULTIPLE_CNT' => 1,
        'SORT' => 70,
    ]);
 };