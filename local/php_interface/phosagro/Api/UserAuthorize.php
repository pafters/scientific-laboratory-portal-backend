<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\System\Api\AccessorFactory;
use Phosagro\System\Api\Error;
use Phosagro\System\Api\Route;

final class UserAuthorize
{
    private const CAPTCHA = 'captcha';
    private const LOGIN = 'login';
    private const PASSWORD = 'password';

    public function __construct(
        private readonly AccessorFactory $accessorFactory,
        private readonly \CMain $bitrix,
        private readonly \CUser $bitrixUser,
        private readonly UserManager $userManager,
    ) {}

    #[Route(method: 'POST', pattern: '~^/api/user/authorize/$~')]
    public function execute(): array
    {
        $accessor = $this->accessorFactory->createFromRequest();

        $accessor->assertNullableCaptchaObject(self::CAPTCHA);
        $accessor->assertStringFilled(self::LOGIN);
        $accessor->assertStringFilled(self::PASSWORD);
        $accessor->checkErrors();

        $login = $accessor->getStringFilled(self::LOGIN);
        $user = $this->userManager->findByLogin($login) ?? $this->userManager->findByEmail($login);

        if (null === $user) {
            $accessor->addErrorInvalid(self::LOGIN);
            $accessor->throwErrors();
        }

        $result = $this->bitrixUser->Login($user->login, $accessor->getStringFilled(self::PASSWORD));
        if (true !== $result) {
            if ($user->blocked) {
                $accessor->addFieldError(self::LOGIN, Error::BLOCKED);
            } elseif (!$user->active) {
                $accessor->addFieldError(self::LOGIN, Error::INACTIVE);
            } elseif ($this->bitrix->NeedCAPTHA()) {
                $accessor->addErrorInvalid(self::CAPTCHA);
            } else {
                $accessor->addErrorInvalid(self::PASSWORD);
            }
        }

        $accessor->checkErrors();

        return $user->toApi();
    }
}
