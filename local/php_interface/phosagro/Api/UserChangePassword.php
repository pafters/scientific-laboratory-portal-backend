<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\System\Api\AccessorFactory;
use Phosagro\System\Api\Errors\CodeRequiredError;
use Phosagro\System\Api\Route;
use Phosagro\System\Array\WrongTypeException;
use Phosagro\User\Constraints\PreventUserActivationWhenChangingPassword;

final class UserChangePassword
{
    use ChangePasswordTrait;

    private const CAPTCHA = 'captcha';
    private const CODE = 'code';
    private const ID = 'id';
    private const LOGIN = 'login';
    private const PASSWORD = 'password';
    private const PASSWORD_CONFIRM = 'password-confirm';
    private const PASSWORD_OLD = 'password-old';
    private const PHONE = 'phone';

    public function __construct(
        private readonly AccessorFactory $accessorFactory,
        private readonly \CUser $bitrixUser,
        private readonly PreventUserActivationWhenChangingPassword $preventUserActivationWhenChangingPassword,
    ) {}

    #[Route(method: 'POST', pattern: '~^/api/user/change-password/$~')]
    public function execute(): array
    {
        $request = $this->accessorFactory->createFromRequest();
        $request->assertNullableObject(self::CAPTCHA);
        $request->assertNullableStringFilled(self::CODE);
        $request->assertStringFilled(self::LOGIN);
        $request->assertStringFilled(self::PASSWORD);
        $request->assertStringFilled(self::PASSWORD_CONFIRM);
        $request->assertNullableStringFilled(self::PASSWORD_OLD);
        $request->assertNullablePhoneNumber(self::PHONE);

        try {
            $captcha = $request->getNullableObject(self::CAPTCHA);
        } catch (WrongTypeException) {
            $request->addErrorInvalid(self::CAPTCHA);
        }

        if (null !== $captcha) {
            $captcha->assertNullableStringFilled(self::CODE);
            $captcha->assertNullableStringFilled(self::ID);
        }

        $request->checkErrors();

        $code = $request->getNullableStringFilled(self::CODE);

        if ('' === $code) {
            throw new CodeRequiredError();
        }

        $this->preventUserActivationWhenChangingPassword->preventActivation();

        $this->changePassword(
            $request,
            $this->bitrixUser,
            (null === $captcha) ? '' : $captcha->getNullableStringFilled(self::CODE),
            (null === $captcha) ? '' : $captcha->getNullableStringFilled(self::ID),
            $request->getNullableStringFilled(self::CODE),
            $request->getStringFilled(self::LOGIN),
            $request->getStringFilled(self::PASSWORD),
            $request->getStringFilled(self::PASSWORD_CONFIRM),
            $request->getNullableStringFilled(self::PASSWORD_OLD),
            $request->getNullablePhoneNumber(self::PHONE) ?? '',
            self::CODE,
            self::LOGIN,
            self::PASSWORD,
            self::PASSWORD_CONFIRM,
            self::PASSWORD_OLD,
        );

        return [];
    }
}
