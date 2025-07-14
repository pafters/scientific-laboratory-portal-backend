<?php

declare(strict_types=1);

use Phosagro\Migration\EmailHelper;

return static function (EmailHelper $emails): void {
    $description = <<<'DESCRIPTION'
    #ADMIN_URL# - ссылка на страницу выполненного задания
    #DEFAULT_EMAIL_FROM# - адрес электронной почты сервера
    #EVENT_NAME# - название события
    #MODERATOR_EMAIL# - адрес электронной почты модератора события
    #SERVER_NAME# - название сайта
    #TASK_NAME# - название задания
    DESCRIPTION;

    $emails->createEmailEventType('EVENT_TASK_FILES_UPLOAD', [
        'DESCRIPTION' => $description,
        'NAME' => 'Выполнено задание с загрузкой файла',
    ]);

    $message = <<<'MESSAGE'
    Информационное сообщение сайта #SERVER_NAME#
    ------------------------------------------

    Выполнено задание "#TASK_NAME#" события "#EVENT_NAME#".

    Требуется модерация загруженных файлов.

    Чтобы признать задание выполненным активируйте его.

    Чтобы признать задание проваленным оставьте его неактивным
    и напишите описание хотя бы к одному файлу.

    Посмотреть загруженные файлы можно по ссылке:
    #ADMIN_URL#

    Письмо сгенерировано автоматически.
    MESSAGE;

    $emails->createEmailEventMessage(
        'EVENT_TASK_FILES_UPLOAD',
        '#SITE_NAME#: Загрузка файлов в событии #EVENT_NAME#',
        [
            'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
            'EMAIL_TO' => '#MODERATOR_EMAIL#',
            'MESSAGE' => $message,
        ],
    );
};
