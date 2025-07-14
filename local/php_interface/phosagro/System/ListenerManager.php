<?php

declare(strict_types=1);

namespace Phosagro\System;

use Bitrix\Main\EventManager;
use Phosagro\ServiceContainer;

final class ListenerManager
{
    public function __construct(
        private readonly EventManager $eventManager,
        private readonly ServiceContainer $serviceContainer,
    ) {}

    public function register(string $class): void
    {
        $instance = $this->serviceContainer->get($class);

        if (!$instance instanceof ListenerInterface) {
            throw new \InvalidArgumentException(sprintf('Class "%s" is not a "%s".', $class, ListenerInterface::class));
        }

        $instance->registerListeners($this->eventManager);
    }
}
