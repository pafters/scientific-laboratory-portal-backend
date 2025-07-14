<?php

declare(strict_types=1);

use Phosagro\Museum\FileCleanerAgent;
use Phosagro\Museum\ScoreAccruerAgent;
use Phosagro\Museum\VisitsUpdaterAgent;
use Phosagro\System\AgentManager;

return static function (AgentManager $agents): void {
    $agents->registerAgent(FileCleanerAgent::class, 601);
    $agents->registerAgent(ScoreAccruerAgent::class, 607);
    $agents->registerAgent(VisitsUpdaterAgent::class, 613);
};
