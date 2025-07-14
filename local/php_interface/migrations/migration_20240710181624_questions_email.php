<?php

declare(strict_types=1);

use Phosagro\Migration\EmailHelper;

return static function (EmailHelper $emails): void {
    $description = <<<'DESCRIPTION'
    #ADMIN_URL# - ссылка на страницу выполненного задания
    #DEFAULT_EMAIL_FROM# - адрес электронной почты сервера
    #MODERATOR_EMAIL# - адрес электронной почты модератора
    #SITE_NAME# - название сайта
    #USER_LOGIN# - имя пользователя на сайте
    DESCRIPTION;

    $emails->createEmailEventType('QUESTION_ADDED', [
        'DESCRIPTION' => $description,
        'NAME' => 'Задан вопрос',
    ]);

    $message = <<<'MESSAGE'
    Информационное сообщение сайта #SITE_NAME#
    ------------------------------------------

    Пользователь #USER_LOGIN# задал вопрос.

    Посмотрите вопрос и напишите ответ в поле «Детальное описание» по ссылке:
    #ADMIN_URL#

    Письмо сгенерировано автоматически.
    MESSAGE;

    $emails->createEmailEventMessage(
        'QUESTION_ADDED',
        '#SITE_NAME#: Новый вопрос',
        [
            'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
            'EMAIL_TO' => '#MODERATOR_EMAIL#',
            'MESSAGE' => $message,
        ],
    );
};