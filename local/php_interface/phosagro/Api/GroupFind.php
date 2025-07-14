<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\GroupManager;
use Phosagro\System\Api\Route;

final class GroupFind
{
    public function __construct(
        private readonly GroupManager $groupManager,
    ) {}

    #[Route(pattern: '~^/api/group/find/$~')]
    public function execute(): array
    {
        /** @var array[] $result */
        $result = [];

        foreach ($this->groupManager->findAll() as $item) {
            $result[] = $item->toApi();
        }

        return $result;
    }
}
