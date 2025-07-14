<?php

declare(strict_types=1);

namespace Phosagro\System;

use Phosagro\ServiceContainer;

use function Phosagro\get_bitrix_error;

final class AgentManager
{
    public function __construct(
        private readonly ServiceContainer $serviceContainer,
    ) {}

    public function execute(string $class): string
    {
        $agent = $this->serviceContainer->get($class);

        if ($agent instanceof AgentInterface) {
            $agent->execute();
        }

        return $this->getCall($class);
    }

    public function registerAgent(string $class, int $interval = 86400): void
    {
        $result = \CAgent::AddAgent($this->getCall($class), interval: $interval);

        if (!$result) {
            throw new \RuntimeException('Can not add agent. '.get_bitrix_error());
        }
    }

    private function getCall(string $class): string
    {
        return sprintf(
            '%s::getInstance()->get(%s)->execute(%s);',
            ServiceContainer::class,
            var_export(self::class, true),
            var_export($class, true),
        );
    }
}
