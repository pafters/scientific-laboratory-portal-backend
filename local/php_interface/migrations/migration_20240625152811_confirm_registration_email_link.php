<?php

declare(strict_types=1);

use Phosagro\Migration\EmailHelper;

return static function (EmailHelper $emails): void {
    $emails->changeEmailMessage('NEW_USER_CONFIRM', '#SITE_NAME#: Подтверждение регистрации нового пользователя', [
        'MESSAGE' => <<<'MESSAGE'
            Информационное сообщение сайта #SITE_NAME#
            ------------------------------------------

            Здравствуйте,

            Вы получили это сообщение, так как ваш адрес был использован при регистрации нового пользователя на сервере #SERVER_NAME#.

            Для подтверждения адреса электронной почты перейдите по ссылке: http://#SERVER_NAME#/auth/confirm-registration/?confirm_registration=yes&confirm_user_id=#USER_ID#&confirm_code=#CONFIRM_CODE#

            ---------------------------------------------------------------------

            Сообщение сгенерировано автоматически.
            MESSAGE,
    ]);
};
