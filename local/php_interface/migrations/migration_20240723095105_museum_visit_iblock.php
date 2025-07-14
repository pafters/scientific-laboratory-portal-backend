<?php

declare(strict_types=1);

use Phosagro\Migration\HighloadblockHelper;
use Phosagro\Migration\UserFieldHelper;

return static function (HighloadblockHelper $highloadblocks, UserFieldHelper $userfields): void {
    $highloadblocks->createHighloadblock('MuseumVisit', 'Museum Visits', 'Посещения музея');

    $entity = $userfields->getEntityForHighloadblock('MuseumVisit');

    $userfields->createUserFieldInteger($entity, 'UF_USER', nameRu: 'Пользователь');
    $userfields->createUserFieldString($entity, 'UF_VISIT', nameRu: 'Посещение');
    $userfields->createUserFieldElement($entity, 'UF_OBJECT', 'directory', 'MuseumObject', nameRu: 'Объект музея');
    $userfields->createUserFieldString($entity, 'UF_STATUS', nameRu: 'Статус посещения');
    $userfields->createUserFieldDatetime($entity, 'UF_DATE', nameRu: 'Дата посещения');
    $userfields->createUserFieldBoolean($entity, 'UF_ACCRUED', nameRu: 'Начислены баллы');
};
