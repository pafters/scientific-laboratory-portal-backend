<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\ScoreManager;
use Phosagro\System\Api\Errors\NotAuthorizedError;
use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;

final class UserGetScore
{
    public function __construct(
        private readonly AuthorizationContext $authorization,
        private readonly ScoreManager $scores,
    ) {}

    #[Route(pattern: '~^/api/user/get\\-score/$~')]
    public function execute(): array
    {
        $user = $this->authorization->getNullableAuthorizedUser();

        if (null === $user) {
            throw new NotAuthorizedError();
        }

        return $this->scores->calculateUserScore($user->userIdentifier)->toApi();
    }
}
