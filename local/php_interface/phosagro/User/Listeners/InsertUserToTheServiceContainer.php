<?php

declare(strict_types=1);

namespace Phosagro\User\Listeners;

use Bitrix\Main\EventManager;
use Phosagro\ServiceContainer;
use Phosagro\System\ListenerInterface;

final class InsertUserToTheServiceContainer implements ListenerInterface
{
    public function __construct(
        private readonly ServiceContainer $serviceContainer,
    ) {}

    public function registerListeners(EventManager $eventManager): void
    {
        $eventManager->addEventHandler('main', 'OnBeforeProlog', function (): void {
            $this->serviceContainer->set(\CUser::class, $GLOBALS['USER']);
        });
    }
}
