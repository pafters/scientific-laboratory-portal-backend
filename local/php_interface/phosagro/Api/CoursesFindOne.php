<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\CourseManager;
use Phosagro\System\Api\Route;

final class CoursesFindOne
{
    public function __construct(
        private CourseManager $courseManager,
    ) {
    }

    #[Route(pattern: '~^/api/courses/(?<id>[^/]+)/$~')]
    public function execute(string $id): array
    {
        $result = $this->courseManager->getOneById($id);

        return $result;
    }
}
