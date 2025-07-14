<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\ContactsManager;
use Phosagro\System\Api\Route;

final class ContactsFindAll
{
    public function __construct(
        private ContactsManager $contactsManager,
    ) {
    }

    #[Route(pattern: '~^/api/contacts/$~')]
    public function execute(): array
    {
        $result = $this->contactsManager->getAll();

        return $result;
    }
}
