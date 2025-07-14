<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\CityManager;
use Phosagro\System\Api\Route;

final class CityFind
{
    public function __construct(
        private readonly CityManager $cityManager,
    ) {}

    #[Route(pattern: '~^/api/city/find/$~')]
    public function execute(): array
    {
        /** @var array[] $result */
        $result = [];

        foreach ($this->cityManager->findAll() as $city) {
            $result[] = $city->toApi();
        }

        return $result;
    }
}
