<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\PartnerManager;
use Phosagro\System\Api\Route;

final class PartnerFind
{
    public function __construct(
        private readonly PartnerManager $partnerManager,
    ) {}

    #[Route(pattern: '~^/api/partner/find/$~')]
    public function execute(): array
    {
        /** @var array[] $result */
        $result = [];

        foreach ($this->partnerManager->findAll() as $item) {
            $result[] = $item->toApi();
        }

        return $result;
    }
}
