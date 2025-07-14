<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\System\Api\AccessorFactory;
use Phosagro\System\Api\Errors\ServerError;
use Phosagro\System\Api\Route;

final class UserResendPhoneConfirmation
{
    private const LOGIN = 'login';
    private const PHONE = 'phone';

    public function __construct(
        private readonly AccessorFactory $accessorFactory,
        private readonly \CUser $bitrixUser,
        private readonly UserManager $userManager,
    ) {}

    #[Route(method: 'POST', pattern: '~^/api/user/resend-phone-confirmation/$~')]
    public function execute(): array
    {
        $accessor = $this->accessorFactory->createFromRequest();
        $accessor->assertStringFilled(self::LOGIN);
        $accessor->assertPhoneNumber(self::PHONE);
        $accessor->checkErrors();

        $user = $this->userManager->findByLogin($accessor->getStringFilled(self::LOGIN));

        if (null === $user) {
            $accessor->addErrorInvalid(self::LOGIN);
        } elseif ($user->phone !== $accessor->getPhoneNumber(self::PHONE)) {
            $accessor->addErrorInvalid(self::LOGIN);
        }

        $accessor->checkErrors();

        $result = $this->bitrixUser->SendPhoneCode(
            $accessor->getPhoneNumber(self::PHONE),
            'SMS_USER_RESTORE_PASSWORD',
        );

        if (!$result->isSuccess()) {
            throw new ServerError($result->getErrorMessages());
        }

        return [];
    }
}
