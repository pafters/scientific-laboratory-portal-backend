<?php

declare(strict_types=1);

use Phosagro\Migration\HighloadblockHelper;
use Phosagro\Migration\UserFieldHelper;

return static function (
    HighloadblockHelper $highloadblockHelper,
    UserFieldHelper $userFieldHelper,
): void {
    $entity = $userFieldHelper->getEntityForHighloadblock('EventParticipant');
    $userFieldHelper->deleteUserField($entity, 'UF_REJECTED_AT');
    $userFieldHelper->deleteUserField($entity, 'UF_REJECTION');
    $userFieldHelper->deleteUserField($entity, 'UF_CONFIRMED_AT');
    $userFieldHelper->deleteUserField($entity, 'UF_CONFIRMED');
    $userFieldHelper->deleteUserField($entity, 'UF_CREATED_AT');
    $userFieldHelper->deleteUserField($entity, 'UF_USER');
    $userFieldHelper->deleteUserField($entity, 'UF_EVENT');
    $highloadblockHelper->deleteHighloadblock('EventParticipant');
};
