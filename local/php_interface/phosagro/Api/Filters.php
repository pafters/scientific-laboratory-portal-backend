<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\FilterManager;
use Phosagro\System\Api\Route;

final class Filters
{
    public function __construct(
        private FilterManager $filterManager,
    ) {
    }

    #[Route(pattern: '~^/api/filters/(?:\\?.*)?$~')]
    public function execute(): array
    {
        $partners = $_GET['partners'] ?? null;
        $cities = $_GET['cities'] ?? null;
        $ages = $_GET['ages'] ?? null;
        $companies = $_GET['companies'] ?? null;

        $result = $this->filterManager->getFilters($partners, $cities, $ages, $companies);

        return $result;
    }
}
