<?php

declare(strict_types=1);

use Phosagro\System\AgentManager;
use Phosagro\User\Agents\NotifyAdminWhenUserRegistered;
use Phosagro\User\Agents\NotifyUserWhenRegistrationAccepted;
use Phosagro\User\Agents\NotifyUserWhenRegistrationRejected;

return static function (AgentManager $agentManager): void {
    $agentManager->registerAgent(NotifyAdminWhenUserRegistered::class, 60);
    $agentManager->registerAgent(NotifyUserWhenRegistrationAccepted::class, 60);
    $agentManager->registerAgent(NotifyUserWhenRegistrationRejected::class, 60);
};
