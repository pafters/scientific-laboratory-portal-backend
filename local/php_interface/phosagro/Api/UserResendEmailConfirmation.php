<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\System\Api\AccessorFactory;
use Phosagro\System\Api\Errors\BadRequestError;
use Phosagro\System\Api\Errors\ServerError;
use Phosagro\System\Api\Route;
use Phosagro\System\Clock;

use function Phosagro\get_bitrix_error;

final class UserResendEmailConfirmation
{
    private const EMAIL = 'email';
    private const LOGIN = 'login';

    public function __construct(
        private readonly AccessorFactory $accessorFactory,
        private readonly \CUser $bitrixUser,
        private readonly Clock $clock,
        private readonly UserManager $userManager,
    ) {}

    #[Route(method: 'POST', pattern: '~^/api/user/resend-email-confirmation/$~')]
    public function execute(): array
    {
        $accessor = $this->accessorFactory->createFromRequest();
        $accessor->assertEmail(self::EMAIL);
        $accessor->assertStringFilled(self::LOGIN);
        $accessor->checkErrors();

        $now = $this->clock->now();

        $user = $this->userManager->findByLogin($accessor->getStringFilled(self::LOGIN));

        if (null === $user) {
            $accessor->addErrorInvalid(self::LOGIN);
        } elseif ($user->email !== $accessor->getEmail(self::EMAIL)) {
            $accessor->addErrorInvalid(self::LOGIN);
        } elseif ('' === $user->confirmCode) {
            throw new BadRequestError('already_confirmed');
        } elseif (($time = $user->getEmailConfirmationRemainingTime($now)) > 0) {
            throw new BadRequestError('already_sent', ['wait' => $time]);
        } else {
            $sendingResult = \CEvent::SendImmediate('NEW_USER_CONFIRM', 's1', [
                'CONFIRM_CODE' => $user->confirmCode,
                'EMAIL' => $user->email,
                'USER_ID' => $this->userManager->getId($user),
            ]);

            if (!$sendingResult) {
                throw new ServerError([get_bitrix_error()]);
            }

            $updateResult = $this->bitrixUser->Update($this->userManager->getId($user), [
                'UF_EMAIL_CONFIRM_REQ' => ConvertTimeStamp($now->getTimestamp(), 'FULL'),
            ]);

            if (!$updateResult) {
                throw new ServerError([$this->bitrixUser->LAST_ERROR]);
            }
        }

        $accessor->checkErrors();

        return [];
    }
}
