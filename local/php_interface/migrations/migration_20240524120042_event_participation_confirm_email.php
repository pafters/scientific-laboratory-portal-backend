<?php

declare(strict_types=1);

use Phosagro\Migration\EmailHelper;

return static function (EmailHelper $emails): void {
    $description = <<<'DESCRIPTION'
    #DEFAULT_EMAIL_FROM# - адрес электронной почты сервера
    #EVENT_NAME# - название события
    #SERVER_NAME# - название сайта
    #USER_EMAIL# - адрес электронной почты подавшего заявку
    DESCRIPTION;

    $emails->createEmailEventType('EVENT_PARTICIPATION_CONFIRM', [
        'DESCRIPTION' => $description,
        'NAME' => 'Подтверждение заявки на участие в событии',
    ]);

    $message = <<<'MESSAGE'
    Информационное сообщение сайта #SERVER_NAME#
    ------------------------------------------

    Ваша заявка на участие в событии "#EVENT_NAME#" подтверждена модератором.

    Письмо сгенерировано автоматически.
    MESSAGE;

    $emails->createEmailEventMessage(
        'EVENT_PARTICIPATION_CONFIRM',
        '#SITE_NAME#: Участие в событии #EVENT_NAME#',
        [
            'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
            'EMAIL_TO' => '#USER_EMAIL#',
            'MESSAGE' => $message,
        ],
    );
};
