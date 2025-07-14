<?php

declare(strict_types=1);

use Phosagro\Migration\GroupHelper;

// В классе GroupIdentifiers написано зачем нужно прописывать группам идентификаторы.

return static function (GroupHelper $groups): void {
    $groups->deleteGroup('event_moderator');

    $groups->deleteGroup('public_moderator');

    $groups->deleteGroup('education_moderator');

    $groups->createGroup('event_moderator', 'Модератор события', [
        'C_SORT' => 10,
        'ID' => 1316715,
    ]);

    $groups->createGroup('public_moderator', 'Модератор публичного портала', [
        'ID' => 1316716,
    ]);

    $groups->createGroup('education_moderator', 'Модератор обучения', [
        'ID ' => 1316717,
    ]);
};
