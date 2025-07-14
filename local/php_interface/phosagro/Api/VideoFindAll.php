<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\VideoManager;

use Phosagro\System\Api\Route;

final class VideoFindAll
{
    public function __construct(
        private VideoManager $videoManager,
    ) {
    }

    #[Route(pattern: '~^/api/videos/(?:\\?.*)?$~')]
    public function execute(): array
    {
        $page = $_GET['page'] ?? "1";
        $counts = $_GET['counts'] ?? "8";

        $result = $this->videoManager->getAll($page, $counts);

        return $result;
    }
}
