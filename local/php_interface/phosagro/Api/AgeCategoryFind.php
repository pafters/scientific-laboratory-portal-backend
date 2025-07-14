<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\AgeCategoryManager;
use Phosagro\System\Api\Route;

final class AgeCategoryFind
{
    public function __construct(
        private readonly AgeCategoryManager $ageCategoryManager,
    ) {}

    #[Route(pattern: '~^/api/age-category/find/$~')]
    public function execute(): array
    {
        /** @var array[] $result */
        $result = [];

        foreach ($this->ageCategoryManager->findAll() as $ageCategory) {
            $result[] = $ageCategory->toApi();
        }

        return $result;
    }
}
