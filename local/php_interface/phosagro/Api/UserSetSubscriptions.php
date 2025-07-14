<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Subscription\RubricCode;
use Phosagro\Subscription\Subscriber;
use Phosagro\Subscription\Subscriptions;
use Phosagro\System\Api\AccessorFactory;
use Phosagro\System\Api\Errors\NotAuthorizedError;
use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;

final class UserSetSubscriptions
{
    public function __construct(
        private readonly AccessorFactory $accessors,
        private readonly AuthorizationContext $authorization,
        private readonly Subscriber $subscriber,
    ) {}

    #[Route(method: 'POST', pattern: '~^/api/user/set\-subscriptions/$~')]
    public function execute(): array
    {
        $user = $this->authorization->getNullableAuthorizedUser();

        if (null === $user) {
            throw new NotAuthorizedError();
        }

        $accessor = $this->accessors->createFromRequest('SUBSCRIPTION_');

        foreach (RubricCode::cases() as $code) {
            $accessor->assertBool($code->getApiCode());
        }

        $accessor->checkErrors();

        $subscriptions = new Subscriptions();

        foreach (RubricCode::cases() as $code) {
            if ($accessor->getBool($code->getApiCode())) {
                $subscriptions->markSubscribed($code);
            } else {
                $subscriptions->markUnsubscribed($code);
            }
        }

        $this->subscriber->setSubscriptions($user, $subscriptions);

        return [];
    }
}
