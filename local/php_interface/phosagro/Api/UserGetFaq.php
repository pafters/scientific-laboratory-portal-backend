<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\FaqManager;
use Phosagro\Object\Faq;
use Phosagro\System\Api\Route;

final class UserGetFaq
{
    public function __construct(
        private readonly FaqManager $faqs,
    ) {}

    #[Route(pattern: '~^/api/faq/find/$~')]
    public function execute(): array
    {
        return [
            'questions' => array_values(
                array_map(
                    static fn (Faq $faq): array => $faq->toApi(),
                    $this->faqs->findAllElements()
                )
            ),
        ];
    }
}
