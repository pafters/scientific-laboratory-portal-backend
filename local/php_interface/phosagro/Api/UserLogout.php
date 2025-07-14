<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;

final class UserLogout
{
    public function __construct(
        private readonly AuthorizationContext $authorization,
    ) {}

    #[Route(pattern: '~^/api/user/logout/$~')]
    public function execute(): array
    {
        $this->authorization->clearAuthorizedUser();

        return [];
    }
}
