<?php

declare(strict_types=1);

use Phosagro\Migration\HighloadblockHelper;
use Phosagro\Migration\UserFieldHelper;

return static function (HighloadblockHelper $highloadblocks, UserFieldHelper $userfields): void {
    $highloadblocks->createHighloadblock('Score', 'Scores', 'Баллы');

    $entity = sprintf('HLBLOCK_%u', $highloadblocks->getHighloadblockId('Score'));

    $userfields->createUserFieldInteger($entity, 'UF_AMOUNT', 'Amount', 'Количество');

    $userfields->createUserFieldString($entity, 'UF_COMMENT', 'Comment', 'Комментарий');

    $userfields->createUserFieldDatetime($entity, 'UF_DATE', 'Date', 'Дата');

    $userfields->createUserFieldInteger($entity, 'UF_REASON', 'Reason', 'Причина');

    $userfields->createUserFieldString($entity, 'UF_SUBJECT', 'Object', 'Объект');

    $userfields->createUserFieldInteger($entity, 'UF_USER', 'User', 'Пользователь');
};
