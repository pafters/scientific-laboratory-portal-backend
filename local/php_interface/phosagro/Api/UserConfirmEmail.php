<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Enum\LogEvent;
use Phosagro\Log\Logger;
use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\Manager\Errors\FoundMultipleException;
use Phosagro\Manager\Errors\NotFoundException;
use Phosagro\Object\Bitrix\User;
use Phosagro\System\Api\AccessorFactory;
use Phosagro\System\Api\Errors\ServerError;
use Phosagro\System\Api\Route;

final class UserConfirmEmail
{
    private const CODE = 'code';
    private const USER = 'user';

    public function __construct(
        private readonly AccessorFactory $accessorFactory,
        private readonly \CUser $bitrixUser,
        private readonly Logger $logger,
        private readonly UserManager $userManager,
    ) {}

    #[Route(method: 'POST', pattern: '~^/api/user/confirm-email/$~')]
    public function execute(): array
    {
        $accessor = $this->accessorFactory->createFromRequest();
        $accessor->assertStringFilled(self::CODE);
        $accessor->assertIntParsed(self::USER);
        $accessor->checkErrors();

        $confirmationCode = $accessor->getStringFilled(self::CODE);
        $userIdentifier = $accessor->getIntParsed(self::USER);

        try {
            $user = $this->userManager->getById($userIdentifier);
        } catch (NotFoundException) {
            $accessor->addErrorInvalid(self::USER);
        } catch (FoundMultipleException $multipleError) {
            throw new ServerError([$multipleError->getMessage()]);
        }

        if ($confirmationCode !== $user->confirmCode) {
            $accessor->addErrorInvalid(self::CODE);
        }

        $accessor->checkErrors();

        $this->confirm($user);

        return [];
    }

    private function confirm(User $user): void
    {
        $userId = $this->userManager->getId($user);

        $result = $this->bitrixUser->Update($userId, [
            'CONFIRM_CODE' => '',
        ]);

        if (!$result) {
            throw new ServerError([$this->bitrixUser->LAST_ERROR]);
        }

        $this->logger->log(LogEvent::USER_EMAIL_CONFIRMED, sprintf('%d', $userId), $user->email);
    }
}
