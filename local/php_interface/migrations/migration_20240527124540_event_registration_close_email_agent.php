<?php

declare(strict_types=1);

use Phosagro\Event\Agents\SendRegistrationCloseEmail;
use Phosagro\System\AgentManager;

return static function (AgentManager $agents): void {
    $agents->registerAgent(SendRegistrationCloseEmail::class, 600);
};
