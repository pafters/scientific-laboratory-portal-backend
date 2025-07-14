<?php

declare(strict_types=1);

use Phosagro\Migration\HighloadblockHelper;
use Phosagro\Migration\UserFieldHelper;

return static function (HighloadblockHelper $highloadblocks, UserFieldHelper $userfields): void {
    $highloadblocks->createHighloadblock('Rating', 'Ratings', 'Рейтинги');

    $entity = sprintf('HLBLOCK_%u', $highloadblocks->getHighloadblockId('Rating'));

    $userfields->createUserFieldDatetime($entity, 'UF_DATE', 'Date', 'Дата');
    $userfields->createUserFieldInteger($entity, 'UF_EVENT', 'Event', 'Событие');
    $userfields->createUserFieldDatetime($entity, 'UF_PERIOD', 'Period', 'Период');
    $userfields->createUserFieldInteger($entity, 'UF_SCORE', 'Score', 'Баллы');
    $userfields->createUserFieldInteger($entity, 'UF_TYPE', 'Type', 'Тип');
    $userfields->createUserFieldInteger($entity, 'UF_USER', 'User', 'Пользователь');
};
