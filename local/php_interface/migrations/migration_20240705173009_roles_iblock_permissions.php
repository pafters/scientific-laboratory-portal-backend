<?php

declare(strict_types=1);

use Phosagro\Migration\IblockPermissionHelper;

return static function (IblockPermissionHelper $permissions): void {
    $permissions->setIblockPermissons('content', 'Contacts', [
        'education_moderator' => 'R',
        'event_moderator' => 'R',
        'guest' => 'R',
        'public_moderator' => 'W',
        'technical_administrator' => 'X',
    ]);

    $permissions->setIblockPermissons('content', 'Course', [
        'education_moderator' => 'R',
        'event_moderator' => 'R',
        'guest' => 'R',
        'public_moderator' => 'W',
        'technical_administrator' => 'X',
    ]);

    $permissions->setIblockPermissons('content', 'Event', [
        'education_moderator' => 'R',
        'event_moderator' => 'W',
        'guest' => 'R',
        'public_moderator' => 'R',
        'technical_administrator' => 'X',
    ]);

    $permissions->setIblockPermissons('content', 'News', [
        'education_moderator' => 'R',
        'event_moderator' => 'R',
        'guest' => 'R',
        'public_moderator' => 'W',
        'technical_administrator' => 'X',
    ]);

    $permissions->setIblockPermissons('content', 'Video', [
        'education_moderator' => 'R',
        'event_moderator' => 'R',
        'guest' => 'R',
        'public_moderator' => 'W',
        'technical_administrator' => 'X',
    ]);

    $permissions->setIblockPermissons('directory', 'AccrualReason', [
        'education_moderator' => 'R',
        'event_moderator' => 'R',
        'guest' => 'R',
        'public_moderator' => 'R',
        'technical_administrator' => 'X',
    ]);

    $permissions->setIblockPermissons('directory', 'AgeCategory', [
        'education_moderator' => 'R',
        'event_moderator' => 'R',
        'guest' => 'R',
        'public_moderator' => 'R',
        'technical_administrator' => 'X',
    ]);

    $permissions->setIblockPermissons('directory', 'City', [
        'education_moderator' => 'R',
        'event_moderator' => 'R',
        'guest' => 'R',
        'public_moderator' => 'R',
        'technical_administrator' => 'X',
    ]);

    $permissions->setIblockPermissons('directory', 'ObsceneWord', [
        'education_moderator' => 'R',
        'event_moderator' => 'R',
        'guest' => 'R',
        'public_moderator' => 'R',
        'technical_administrator' => 'X',
    ]);

    $permissions->setIblockPermissons('directory', 'Partner', [
        'education_moderator' => 'R',
        'event_moderator' => 'R',
        'guest' => 'R',
        'public_moderator' => 'R',
        'technical_administrator' => 'X',
    ]);

    $permissions->setIblockPermissons('directory', 'PhosagroCompany', [
        'education_moderator' => 'R',
        'event_moderator' => 'R',
        'guest' => 'R',
        'public_moderator' => 'R',
        'technical_administrator' => 'X',
    ]);

    $permissions->setIblockPermissons('directory', 'QuestionTopic', [
        'education_moderator' => 'R',
        'event_moderator' => 'R',
        'guest' => 'R',
        'public_moderator' => 'R',
        'technical_administrator' => 'X',
    ]);

    $permissions->setIblockPermissons('directory', 'TaskType', [
        'education_moderator' => 'R',
        'event_moderator' => 'R',
        'guest' => 'R',
        'public_moderator' => 'R',
        'technical_administrator' => 'X',
    ]);

    $permissions->setIblockPermissons('directory', 'UserGroup', [
        'education_moderator' => 'R',
        'event_moderator' => 'W',
        'guest' => 'R',
        'public_moderator' => 'R',
        'technical_administrator' => 'X',
    ]);

    $permissions->setIblockPermissons('event', 'Completion', [
        'education_moderator' => 'R',
        'event_moderator' => 'W',
        'guest' => 'R',
        'public_moderator' => 'R',
        'technical_administrator' => 'X',
    ]);

    $permissions->setIblockPermissons('event', 'Participant', [
        'education_moderator' => 'R',
        'event_moderator' => 'W',
        'guest' => 'R',
        'public_moderator' => 'R',
        'technical_administrator' => 'X',
    ]);

    $permissions->setIblockPermissons('event', 'Task', [
        'education_moderator' => 'R',
        'event_moderator' => 'W',
        'guest' => 'R',
        'public_moderator' => 'R',
        'technical_administrator' => 'X',
    ]);

    $permissions->setIblockPermissons('event', 'Template', [
        'education_moderator' => 'R',
        'event_moderator' => 'R',
        'guest' => 'R',
        'public_moderator' => 'R',
        'technical_administrator' => 'X',
    ]);
};
