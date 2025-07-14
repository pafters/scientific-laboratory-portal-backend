<?php

declare(strict_types=1);

use Phosagro\Rating\RatingCleanerAgent;
use Phosagro\Rating\RatingUpdaterAgent;
use Phosagro\System\AgentManager;

return static function (AgentManager $agentManager): void {
    $agentManager->registerAgent(RatingCleanerAgent::class, 604800);
    $agentManager->registerAgent(RatingUpdaterAgent::class, 600);
};
