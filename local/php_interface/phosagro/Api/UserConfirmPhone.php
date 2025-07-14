<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Enum\LogEvent;
use Phosagro\Log\Logger;
use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\System\Api\AccessorFactory;
use Phosagro\System\Api\Route;

final class UserConfirmPhone
{
    private const CODE = 'code';
    private const LOGIN = 'login';
    private const PHONE = 'phone';

    public function __construct(
        private readonly AccessorFactory $accessorFactory,
        private readonly \CUser $bitrixUser,
        private readonly Logger $logger,
        private readonly UserManager $userManager,
    ) {}

    #[Route(method: 'POST', pattern: '~^/api/user/confirm-phone/$~')]
    public function execute(): array
    {
        $accessor = $this->accessorFactory->createFromRequest();

        $accessor->assertStringFilled(self::CODE);
        $accessor->assertStringFilled(self::LOGIN);
        $accessor->assertPhoneNumber(self::PHONE);

        $accessor->checkErrors();

        $login = $accessor->getStringFilled(self::LOGIN);
        $user = $this->userManager->findByLogin($login);

        if (null === $user) {
            $accessor->addErrorInvalid(self::LOGIN);
            $accessor->throwErrors();
        }

        $phone = $accessor->getPhoneNumber(self::PHONE);

        if ($user->phone !== $phone) {
            $accessor->addErrorInvalid(self::LOGIN);
            $accessor->throwErrors();
        }

        $userId = $this->bitrixUser->VerifyPhoneCode(
            $phone,
            $accessor->getStringFilled(self::CODE),
        );

        if (!$userId) {
            $accessor->addErrorInvalid(self::CODE);
            $accessor->throwErrors();
        }

        $this->logger->log(LogEvent::USER_PHONE_CONFIRMED, sprintf('%d', $userId), $phone);

        return [];
    }
}
