<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\FeedsManager;

use Phosagro\System\Api\Route;

final class FeedsFindAll
{
    public function __construct(
        private FeedsManager $feedsManager,
    ) {
    }

    #[Route(pattern: '~^/api/feeds/(?:\\?.*)?$~')]
    public function execute(): array
    {
        $page = $_GET['page'] ?? "1";
        $counts = $_GET['counts'] ?? "8";
        $city = $_GET['city'] ?? null;

        $result = $this->feedsManager->getAll($page, $counts, $city);

        return $result;
    }
}
