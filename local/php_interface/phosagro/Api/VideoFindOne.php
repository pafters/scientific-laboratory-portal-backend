<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\VideoManager;
use Phosagro\System\Api\Route;

final class VideoFindOne
{
    public function __construct(
        private VideoManager $videoManager,
    ) {
    }

    #[Route(pattern: '~^/api/videos/(?<id>[^/]+)/$~')]
    public function execute(string $id): array
    {
        $result = $this->videoManager->getOneById($id);

        return $result;
    }
}
