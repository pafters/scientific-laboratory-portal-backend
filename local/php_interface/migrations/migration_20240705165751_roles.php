<?php

declare(strict_types=1);

use Phosagro\Migration\GroupHelper;

return static function (GroupHelper $groups): void {
    $groups->assignGroup('technical_administrator', 1);
    $groups->updateGroup('technical_administrator', ['NAME' => 'Технический администратор']);
    $groups->assignGroup('guest', 2);
    $groups->updateGroup('guest', ['NAME' => 'Гость']);
    $groups->createGroup('event_moderator', 'Модератор события', ['C_SORT' => 10]);
    $groups->createGroup('public_moderator', 'Модератор публичного портала');
    $groups->createGroup('education_moderator', 'Модератор обучения');
};
