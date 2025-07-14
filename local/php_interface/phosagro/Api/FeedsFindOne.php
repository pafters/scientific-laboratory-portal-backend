<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\FeedsManager;
use Phosagro\System\Api\Route;

final class FeedsFindOne
{
    public function __construct(
        private FeedsManager $feedsManager,
    ) {
    }

    #[Route(pattern: '~^/api/feeds/(?<id>[^/]+)/$~')]
    public function execute(string $id): array
    {
        $result = $this->feedsManager->getOneById($id);

        return $result;
    }
}
