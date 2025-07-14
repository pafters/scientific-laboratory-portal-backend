<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\CourseManager;
use Phosagro\System\Api\Route;

final class CoursesFindAll
{
    public function __construct(
        private CourseManager $courseManager,
    ) {
    }

    #[Route(pattern: '~^/api/courses/(?:\\?.*)?$~')]
    public function execute(): array
    {
        $page = $_GET['page'] ?? "1";
        $counts = $_GET['counts'] ?? "8";
        $age = $_GET['age'] ?? null;

        $result = $this->courseManager->getAll($page, $counts, $age);

        return $result;
    }
}
