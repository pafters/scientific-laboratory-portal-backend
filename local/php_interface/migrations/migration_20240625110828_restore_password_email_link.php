<?php

declare(strict_types=1);

use Phosagro\Migration\EmailHelper;

return static function (EmailHelper $emails): void {
    $emails->changeEmailMessage('USER_PASS_REQUEST', '#SITE_NAME#: Запрос на смену пароля', [
        'MESSAGE' => <<<'MESSAGE'
            Информационное сообщение сайта #SITE_NAME#
            ------------------------------------------
            #NAME# #LAST_NAME#,

            #MESSAGE#

            Для смены пароля перейдите по следующей ссылке:
            http://#SERVER_NAME#/auth/change-password/?change_password=yes&lang=ru&USER_CHECKWORD=#CHECKWORD#&USER_LOGIN=#URL_LOGIN#

            Ваша регистрационная информация:

            ID пользователя: #USER_ID#
            Статус профиля: #STATUS#
            Login: #LOGIN#

            Сообщение сгенерировано автоматически.
            MESSAGE,
    ]);
};
