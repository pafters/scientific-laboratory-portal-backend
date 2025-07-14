<?php

declare(strict_types=1);

use Phosagro\Migration\IblockHelper;
use Phosagro\Migration\IblockPermissionHelper;
use Phosagro\Migration\IblockPropertyHelper;
use Phosagro\Migration\IblockTypeHelper;

return static function (
    IblockHelper $iblocks,
    IblockPermissionHelper $permissions,
    IblockPropertyHelper $properties,
    IblockTypeHelper $types,
): void {
    $types->createIblockType('voting', 'Voting', 'Голосования');

    $iblocks->createIblock('voting', 'Voting', 'Голосование', 'Голосование', 'Голосования', [
        'FIELDS' => [
            'ACTIVE_FROM' => ['IS_REQUIRED' => 'Y'],
            'ACTIVE_TO' => ['IS_REQUIRED' => 'Y'],
            'DETAIL_TEXT' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_ADD' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_DELETE' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_EDIT' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_ADD' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_DELETE' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_EDIT' => ['IS_REQUIRED' => 'Y'],
        ],
    ]);

    $properties->createPropertyBool('voting', 'Voting', 'MAILED', 'Прекратить рассылку');

    $properties->createPropertyUser('voting', 'Voting', 'OWNER', 'Автор голосования', true);

    $properties->createPropertyNumber('voting', 'Voting', 'VOTING', 'Идентификатор голосования', true);

    $properties->createPropertyNumber('voting', 'Voting', 'LIMIT', 'Количество возможных вариантов для выбора', true);

    $properties->createPropertyElement(
        'voting',
        'Voting',
        'AGE_CATEGORY',
        'Возрастное ограничение',
        'directory',
        'AgeCategory',
        true,
    );

    $properties->createPropertyElement(
        'voting',
        'Voting',
        'EVENT',
        'Ссылка на событие',
        'content',
        'Event',
    );

    $properties->createPropertyElement(
        'voting',
        'Voting',
        'GROUPS',
        'Группы пользователей',
        'directory',
        'UserGroup',
        false,
        [
            'MULTIPLE' => 'Y',
            'MULTIPLE_CNT' => 1,
        ],
    );

    $properties->createPropertyFile('voting', 'Voting', 'FILES', 'Файлы', false, [
        'MULTIPLE' => 'Y',
        'MULTIPLE_CNT' => 1,
    ]);

    $permissions->setIblockPermissons('voting', 'Voting', [
        'education_moderator' => 'W',
        'event_moderator' => 'R',
        'guest' => 'R',
        'public_moderator' => 'R',
        'technical_administrator' => 'X',
    ]);
};
