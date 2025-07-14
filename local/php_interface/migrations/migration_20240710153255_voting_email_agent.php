<?php

declare(strict_types=1);

use Phosagro\System\AgentManager;
use Phosagro\Voting\SubscriptionPostingBuilder;

return static function (AgentManager $agents): void {
    $agents->registerAgent(SubscriptionPostingBuilder::class, 60);
};
