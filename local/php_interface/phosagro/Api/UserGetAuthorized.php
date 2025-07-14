<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\System\Api\Errors\NotAuthorizedError;
use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;

final class UserGetAuthorized
{
    public function __construct(
        private readonly AuthorizationContext $authorization,
        private readonly UserManager $userManager,
    ) {}

    #[Route(pattern: '~^/api/user/get-authorized/$~')]
    public function execute(): array
    {
        $user = $this->authorization->getNullableAuthorizedUser();

        if (null === $user) {
            throw new NotAuthorizedError();
        }

        return $user->toApi();
    }
}
