<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Converter\EventToApiConverter;
use Phosagro\Manager\EventManager;
use Phosagro\Manager\GroupManager;
use Phosagro\System\Api\AccessorFactory;
use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;

final class EventGet
{
    private const ID = 'id';

    public function __construct(
        private readonly AccessorFactory $accessorFactory,
        private readonly AuthorizationContext $authorizationContext,
        private readonly EventManager $eventManager,
        private readonly EventToApiConverter $eventToApiConverter,
        private readonly GroupManager $groupManager,
    ) {}

    #[Route(pattern: '~^/api/event/get/(?<id>[^/]+)/$~')]
    public function execute(string $id): array
    {
        $user = $this->authorizationContext->getNullableAuthorizedUser();

        $accessor = $this->accessorFactory->createFromArray(compact('id'));

        $accessor->assertIntParsed(self::ID);
        $accessor->checkErrors();

        if (null !== $user) {
            $groups = $this->groupManager->findGroupsForUser($user->userIdentifier);
        } else {
            $groups = [];
        }

        $event = $this->eventManager->findEventsByBitrixId(
            active: true,
            bitrixId: $accessor->getIntParsed(self::ID),
            groupList: $groups,
        );

        if (null === $event) {
            $accessor->addErrorInvalid(self::ID);
            $accessor->checkErrors();

            return [];
        }

        return $this->eventToApiConverter->convertEventsToApi($event)[$event];
    }
}
