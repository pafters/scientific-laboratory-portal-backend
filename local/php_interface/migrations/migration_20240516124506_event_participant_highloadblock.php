<?php

declare(strict_types=1);

use Phosagro\Migration\HighloadblockHelper;
use Phosagro\Migration\UserFieldHelper;

return static function (
    HighloadblockHelper $highloadblockHelper,
    UserFieldHelper $userFieldHelper,
): void {
    $highloadblockHelper->createHighloadblock('EventParticipant');

    $userFieldHelper->createUserFieldInteger(
        $userFieldHelper->getEntityForHighloadblock('EventParticipant'),
        'UF_EVENT',
        'Event',
        'Событие',
        'Filled in automatically when submitting an application.',
        'Заполняется автоматически при подаче заявки.',
    );

    $userFieldHelper->createUserFieldInteger(
        $userFieldHelper->getEntityForHighloadblock('EventParticipant'),
        'UF_USER',
        'User',
        'Пользователь',
        'Filled in automatically when submitting an application.',
        'Заполняется автоматически при подаче заявки.',
    );

    $userFieldHelper->createUserFieldDatetime(
        $userFieldHelper->getEntityForHighloadblock('EventParticipant'),
        'UF_CREATED_AT',
        'Application date',
        'Дата заявки',
        'Filled in automatically when submitting an application.',
        'Заполняется автоматически при подаче заявки.',
    );

    $userFieldHelper->createUserFieldBoolean(
        $userFieldHelper->getEntityForHighloadblock('EventParticipant'),
        'UF_CONFIRMED',
        'Confirmed',
        'Подтверждено',
        'Filled by the moderator.',
        'Заполняется модератором.',
    );

    $userFieldHelper->createUserFieldDatetime(
        $userFieldHelper->getEntityForHighloadblock('EventParticipant'),
        'UF_CONFIRMED_AT',
        'Confirmation date',
        'Дата подтверждения',
        'Filled in automatically when confirming an application.',
        'Заполняется автоматически при подтверждении заявки.',
    );

    $userFieldHelper->createUserFieldString(
        $userFieldHelper->getEntityForHighloadblock('EventParticipant'),
        'UF_REJECTION',
        'Rejection reason',
        'Причина отклонения',
        'Filled by the moderator.',
        'Заполняется модератором.',
    );

    $userFieldHelper->createUserFieldDatetime(
        $userFieldHelper->getEntityForHighloadblock('EventParticipant'),
        'UF_REJECTED_AT',
        'Rejection date',
        'Дата отклонения',
        'Filled in automatically when rejecting an application.',
        'Заполняется автоматически при отклонении заявки.',
    );
};
