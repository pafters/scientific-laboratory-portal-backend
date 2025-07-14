<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\System\Api\Accessor;
use Phosagro\System\Api\Errors\CaptchaRequiredError;
use Phosagro\System\Api\Errors\CodeRequiredError;
use Phosagro\System\Api\Errors\ServerError;
use Phosagro\Util\Text;

trait ChangePasswordTrait
{
    public function changePassword(
        Accessor $request,
        \CUser $manager,
        string $captchaCode = '',
        string $captchaSid = '',
        string $checkword = '',
        string $login = '',
        string $password = '',
        string $passwordConfirm = '',
        string $passwordOld = '',
        string $phone = '',
        string $fieldCheckword = '',
        string $fieldLogin = '',
        string $fieldPassword = '',
        string $fieldPasswordConfirm = '',
        string $fieldPasswordOld = '',
    ): void {
        $result = $manager->ChangePassword(
            $login,
            $checkword,
            $password,
            $passwordConfirm,
            's1',
            $captchaCode,
            $captchaSid,
            true,
            $phone,
            $passwordOld,
        );

        $resultType = $result['TYPE'] ?? null;

        if ('ERROR' === $resultType) {
            $messageList = self::parseMessage($result['MESSAGE'] ?? null);

            $codeMessages = self::fetchMessages($messageList, [
                [GetMessage('CHECKWORD_EXPIRE')],
                [GetMessage('CHECKWORD_INCORRECT1')],
                [GetMessage('main_change_pass_code_error')],
            ]);

            if ([] !== $codeMessages) {
                if ('' === $fieldCheckword) {
                    throw new ServerError($codeMessages);
                }
                $request->addErrorInvalid($fieldCheckword, $codeMessages);
            }

            $loginMessages = self::fetchMessages($messageList, [
                [GetMessage('LOGIN_NOT_FOUND1')],
                [GetMessage('MIN_LOGIN')],
            ]);

            if ([] !== $loginMessages) {
                if ('' === $fieldLogin) {
                    throw new ServerError($loginMessages);
                }
                $request->addErrorInvalid($fieldLogin, $loginMessages);
            }

            $passwordMessages = self::fetchMessages($messageList, [
                [GetMessage('MAIN_FUNCTION_REGISTER_PASSWORD_DIGITS')],
                [GetMessage('MAIN_FUNCTION_REGISTER_PASSWORD_LENGTH', '#LENGTH#')],
                [GetMessage('MAIN_FUNCTION_REGISTER_PASSWORD_LOWERCASE')],
                [GetMessage('MAIN_FUNCTION_REGISTER_PASSWORD_PUNCTUATION'), '#SPECIAL_CHARS#'],
                [GetMessage('MAIN_FUNCTION_REGISTER_PASSWORD_UNIQUE')],
                [GetMessage('MAIN_FUNCTION_REGISTER_PASSWORD_UPPERCASE')],
                [GetMessage('main_check_password_weak')],
            ]);

            if ([] !== $passwordMessages) {
                if ('' === $fieldPassword) {
                    throw new ServerError($passwordMessages);
                }
                $request->addErrorInvalid($fieldPassword, $passwordMessages);
            }

            $passwordConfirmMessages = self::fetchMessages($messageList, [
                [GetMessage('WRONG_CONFIRMATION')],
            ]);

            if ([] !== $passwordConfirmMessages) {
                if ('' === $fieldPasswordConfirm) {
                    throw new ServerError($passwordConfirmMessages);
                }
                $request->addErrorInvalid($fieldPasswordConfirm, $passwordConfirmMessages);
            }

            $passwordOldMessages = self::fetchMessages($messageList, [
                [GetMessage('main_change_pass_incorrect_pass')],
            ]);

            if ([] !== $passwordOldMessages) {
                if ('' === $fieldPasswordOld) {
                    throw new ServerError($passwordOldMessages);
                }
                $request->addErrorInvalid($fieldPasswordOld, $passwordOldMessages);
            }

            $request->checkErrors();

            if (self::findMessage($messageList, GetMessage('main_user_captcha_error')) >= 0) {
                throw new CaptchaRequiredError();
            }

            if (self::findMessage($messageList, GetMessage('main_change_pass_empty_checkword')) >= 0) {
                throw new CodeRequiredError();
            }

            throw new ServerError($messageList);
        }

        if ('OK' !== $resultType) {
            throw new \LogicException(sprintf('Unknown bitrix result type "%s".', $resultType));
        }
    }

    /**
     * @param string[]                     $messageList
     * @param array<int,array<int,string>> $fetchList
     *
     * @return string[]
     */
    private static function fetchMessages(array &$messageList, array $fetchList): array
    {
        /** @var string[] $foundList */
        $foundList = [];

        foreach ($fetchList as $search) {
            $index = self::findMessage($messageList, $search[0], ...\array_slice($search, 1));

            if ($index >= 0) {
                foreach (array_splice($messageList, $index, 1) as $found) {
                    $foundList[] = $found;
                }
            }
        }

        return $foundList;
    }

    private static function findMessage(array $messageList, string $message, string ...$parameters): int
    {
        $pattern = trim($message);

        if ([] === $parameters) {
            $index = array_search($pattern, $messageList, true);

            return (false === $index) ? -1 : $index;
        }

        foreach ($parameters as $placeholder) {
            $pattern = Text::replace($message, $placeholder, '.*?');
        }

        $pattern = preg_quote($pattern, '~');
        $pattern = "~^{$pattern}$~";

        foreach ($messageList as $index => $checked) {
            if (null !== Text::match($checked, $pattern)) {
                return $index;
            }
        }

        return -1;
    }

    /**
     * @param mixed $message
     *
     * @return string[]
     */
    private static function parseMessage($message): array
    {
        /** @var string[] $messageList */
        $messageList = [];

        if (\is_string($message)) {
            foreach (explode('<br>', trim($message)) as $piece) {
                $piece = trim($piece);
                if ('' !== $piece) {
                    $messageList[] = $piece;
                }
            }
        }

        return $messageList;
    }
}
