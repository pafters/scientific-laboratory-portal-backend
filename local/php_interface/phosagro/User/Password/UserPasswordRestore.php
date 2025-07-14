<?php

declare(strict_types=1);

namespace Phosagro\User\Password;

use Bitrix\Main\Config\Option;
use Phosagro\Captcha\Captcha;
use Phosagro\Captcha\Exceptions\CaptchaIsWrongException;
use Phosagro\Object\Bitrix\User;
use Phosagro\User\Exceptions\UserIsBlockedException;
use Phosagro\User\Password\Exceptions\SendingIsStoppedException;

final class UserPasswordRestore
{
    /**
     * @throws CaptchaIsWrongException
     * @throws UserIsBlockedException
     */
    public function sendPasswordRestoreEmail(User $user, Captcha $captcha): void
    {
        /*
         * Скопировано из \CUser::SendPassword потому что \CUser::SendPassword
         * отправляет письмо подтверждения регистрации когда пользователь
         * не активен, а у нас по ТЗ пользователь активируется модератором,
         * то есть пользователь пробует восстановить пароль, а вместо письма
         * со ссылкой восстановления пароля получает ссылку где написано
         * что email подтверждён, пробует восстановить пароль снова,
         * и опять получает сообщение email подтверждён.
         */

        global $APPLICATION;

        $params = [
            'LOGIN' => $user->login,
            'EMAIL' => $user->email,
            'SITE_ID' => false,
            'PHONE_NUMBER' => '',
            'SHORT_CODE' => false,
        ];

        $APPLICATION->ResetException();

        foreach (GetModuleEvents('main', 'OnBeforeUserSendPassword', true) as $arEvent) {
            if (false === ExecuteModuleEventEx($arEvent, [&$params])) {
                if ($err = $APPLICATION->GetException()) {
                    throw new SendingIsStoppedException((string) $err->GetString());
                }

                throw new SendingIsStoppedException();
            }
        }

        if ('Y' === Option::get('main', 'captcha_restoring_password', 'N')) {
            if (!$APPLICATION->CaptchaCheckCode($captcha->code, $captcha->sid)) {
                throw new CaptchaIsWrongException();
            }
        }

        if (\defined('ADMIN_SECTION') && (ADMIN_SECTION === true)) {
            $siteId = \CSite::GetDefSite($user->userLid);
        } else {
            $siteId = SITE_ID;
        }

        if ($user->blocked) {
            throw new UserIsBlockedException();
        }

        \CUser::SendUserInfo(
            $user->userIdentifier,
            $siteId,
            GetMessage('INFO_REQ'),
            true,
            'USER_PASS_REQUEST',
        );

        if ('Y' === Option::get('main', 'event_log_password_request', 'N')) {
            \CEventLog::Log('SECURITY', 'USER_INFO', 'main', $user->userIdentifier);
        }
    }
}
