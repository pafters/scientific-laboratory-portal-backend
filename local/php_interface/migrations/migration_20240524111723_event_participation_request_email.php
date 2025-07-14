<?php

declare(strict_types=1);

use Phosagro\Migration\EmailHelper;

return static function (EmailHelper $emails): void {
    $description = <<<'DESCRIPTION'
    #ADMIN_URL# - адрес страницы заявки
    #DEFAULT_EMAIL_FROM# - адрес электронной почты сервера
    #EVENT_NAME# - название события
    #MODERATOR_EMAIL# - адрес электронной почты модератора
    #SERVER_NAME# - название сайта
    #USER_EMAIL# - адрес электронной почты подавшего заявку
    #USER_LOGIN# - логин подавшего заявку
    DESCRIPTION;

    $emails->createEmailEventType('EVENT_PARTICIPATION_REQUEST', [
        'DESCRIPTION' => $description,
        'NAME' => 'Подача заявки на участие в событии',
    ]);

    $message = <<<'MESSAGE'
    Информационное сообщение сайта #SERVER_NAME#
    ------------------------------------------

    Подана заявка на участие в событии "#EVENT_NAME#".

    Событие: #EVENT_NAME#
    Пользователь: #USER_LOGIN#
    Заявка: #ADMIN_URL#

    Проверьте заявку и активируйте либо отклоните её.

    Письмо сгенерировано автоматически.
    MESSAGE;

    $emails->createEmailEventMessage(
        'EVENT_PARTICIPATION_REQUEST',
        '#SITE_NAME#: Заявка на участие в событии #EVENT_NAME#',
        [
            'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
            'EMAIL_TO' => '#MODERATOR_EMAIL#',
            'MESSAGE' => $message,
        ],
    );

    $message = <<<'MESSAGE'
    Информационное сообщение сайта #SERVER_NAME#
    ------------------------------------------

    Ваша заявка на участие в событии "#EVENT_NAME#" ждёт одобрения модератором.

    Письмо сгенерировано автоматически.
    MESSAGE;

    $emails->createEmailEventMessage(
        'EVENT_PARTICIPATION_REQUEST',
        '#SITE_NAME#: Участие в событии #EVENT_NAME#',
        [
            'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
            'EMAIL_TO' => '#USER_EMAIL#',
            'MESSAGE' => $message,
        ],
    );
};
