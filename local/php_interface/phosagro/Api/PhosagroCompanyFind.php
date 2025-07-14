<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\PhosagroCompanyManager;
use Phosagro\System\Api\Route;

final class PhosagroCompanyFind
{
    public function __construct(
        private readonly PhosagroCompanyManager $phosagroCompanyManager,
    ) {}

    #[Route(pattern: '~^/api/phosagro-company/find/$~')]
    public function execute(): array
    {
        $result = [];

        foreach ($this->phosagroCompanyManager->findAll() as $phosagroCompany) {
            $result[] = $phosagroCompany->toApi();
        }

        return $result;
    }
}
