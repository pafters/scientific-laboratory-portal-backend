<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Bitrix\Main\DB\Connection;
use Phosagro\Captcha\Exceptions\CaptchaIsWrongException;
use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\Manager\Errors\FoundMultipleException;
use Phosagro\Manager\Errors\NotFoundException;
use Phosagro\System\Api\AccessorFactory;
use Phosagro\System\Api\Errors\CaptchaRequiredError;
use Phosagro\System\Api\Errors\ServerError;
use Phosagro\System\Api\Route;
use Phosagro\User\Exceptions\UserIsBlockedException;
use Phosagro\User\Password\Exceptions\SendingIsStoppedException;
use Phosagro\User\Password\UserPasswordRestore;

final class UserSendPasswordRestoreEmail
{
    private const CAPTCHA = 'captcha';
    private const EMAIL = 'email';
    private const LOGIN = 'login';

    public function __construct(
        private readonly AccessorFactory $accessors,
        private readonly \CUser $manager,
        private readonly Connection $database,
        private readonly UserManager $users,
        private readonly UserPasswordRestore $userPasswordRestore,
    ) {}

    #[Route(method: 'POST', pattern: '~^/api/user/send\-password\-restore\-email/$~')]
    public function execute(): array
    {
        $accessor = $this->accessors->createFromRequest();

        $accessor->assertNullableCaptchaObject(self::CAPTCHA);

        $emailUser = null;

        $accessor->assertNullableEmail(self::EMAIL);
        if (!$accessor->hasFieldError(self::EMAIL)) {
            $email = $accessor->getNullableEmail(self::EMAIL);
            if (null !== $email) {
                try {
                    $emailUser = $this->users->getByEmail($email);
                } catch (FoundMultipleException) {
                    $accessor->addErrorDuplicate(self::EMAIL);
                } catch (NotFoundException) {
                    $accessor->addErrorUnexpected(self::EMAIL);
                }
            }
        }

        $loginUser = null;

        $accessor->assertNullableStringTrimmed(self::LOGIN);
        if (!$accessor->hasFieldError(self::LOGIN)) {
            $login = $accessor->getNullableStringTrimmed(self::LOGIN);
            if ('' !== $login) {
                try {
                    $loginUser = $this->users->getByLogin($login);
                } catch (FoundMultipleException) {
                    $accessor->addErrorDuplicate(self::LOGIN);
                } catch (NotFoundException) {
                    $accessor->addErrorUnexpected(self::LOGIN);
                }
            }
        }

        $accessor->checkErrors();

        if ((null === $emailUser) && (null === $loginUser)) {
            $accessor->addErrorRequiredAny(self::EMAIL);
            $accessor->addErrorRequiredAny(self::LOGIN);
            $accessor->throwErrors();
        }

        if ($emailUser->userIdentifier !== $loginUser->userIdentifier) {
            $accessor->addErrorRequiredOne(self::EMAIL);
            $accessor->addErrorRequiredOne(self::LOGIN);
            $accessor->throwErrors();
        }

        $captcha = $accessor->getNullableCaptchaObject(self::CAPTCHA);

        try {
            $this->userPasswordRestore->sendPasswordRestoreEmail($emailUser, $captcha);
        } catch (CaptchaIsWrongException) {
            throw new CaptchaRequiredError();
        } catch (SendingIsStoppedException $stoppedError) {
            throw new ServerError([$stoppedError->getMessage()]);
        } catch (UserIsBlockedException $blockedError) {
            throw new ServerError([$blockedError->getMessage()]);
        }

        return [
            'login' => $emailUser->login,
        ];
    }
}
