<?php

declare(strict_types=1);

use Phosagro\Migration\EmailHelper;

return static function (EmailHelper $emails): void {
    $description = <<<'DESCRIPTION'
    #DEFAULT_EMAIL_FROM# - адрес электронной почты сервера
    #EVENT_NAME# - название события
    #MODERATOR_EMAIL# - адрес электронной почты модератора события
    #PARTICIPANT_LIST# - список участников
    #SERVER_NAME# - название сайта
    DESCRIPTION;

    $emails->createEmailEventType('EVENT_REGISTRATION_CLOSE', [
        'DESCRIPTION' => $description,
        'NAME' => 'Завершение регистрации на событие',
    ]);

    $message = <<<'MESSAGE'
    Информационное сообщение сайта #SERVER_NAME#
    ------------------------------------------

    Завершена регистрация на событие "#EVENT_NAME#". Список участников:

    #PARTICIPANT_LIST#

    Письмо сгенерировано автоматически.
    MESSAGE;

    $emails->createEmailEventMessage(
        'EVENT_REGISTRATION_CLOSE',
        '#SITE_NAME#: Список участников события #EVENT_NAME#',
        [
            'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
            'EMAIL_TO' => '#MODERATOR_EMAIL#',
            'MESSAGE' => $message,
        ],
    );
};
