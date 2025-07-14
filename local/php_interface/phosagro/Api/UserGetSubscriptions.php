<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Subscription\Subscriber;
use Phosagro\System\Api\Errors\NotAuthorizedError;
use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;

final class UserGetSubscriptions
{
    public function __construct(
        private readonly AuthorizationContext $authorization,
        private readonly Subscriber $subscriber,
    ) {}

    #[Route(pattern: '~^/api/user/get\-subscriptions/$~')]
    public function execute(): array
    {
        $user = $this->authorization->getNullableAuthorizedUser();

        if (null === $user) {
            throw new NotAuthorizedError();
        }

        return $this->subscriber->getSubscriptions($user)->toApi();
    }
}
