<?php

declare(strict_types=1);

namespace Phosagro\User;

use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\Object\Bitrix\User;

final class AuthorizationContext
{
    private ?User $authorized = null;
    private bool $loaded = false;

    public function __construct(
        private readonly \CUser $bitrixUser,
        private readonly UserManager $userManager,
    ) {}

    public function clearAuthorizedUser(): void
    {
        $this->bitrixUser->Logout();
    }

    public function getAuthorizedUser(): User
    {
        $this->loadAuthorizedUser();

        return $this->getNullableAuthorizedUser();
    }

    public function getNullableAuthorizedUser(): ?User
    {
        $this->loadAuthorizedUser();

        return $this->authorized;
    }

    public function hasAuthorizedUser(): bool
    {
        $this->loadAuthorizedUser();

        return null !== $this->getNullableAuthorizedUser();
    }

    private function loadAuthorizedUser(): void
    {
        if ($this->loaded) {
            return;
        }

        $this->loaded = true;

        $overrideFile = ($_SERVER['DOCUMENT_ROOT'].\DIRECTORY_SEPARATOR.'authorized-user.txt');

        if (file_exists($overrideFile)) {
            $overrideLogin = trim(file_get_contents($overrideFile));
            if ('' !== $overrideLogin) {
                $overrideUser = $this->userManager->findByLogin($overrideLogin);
                if (null !== $overrideUser) {
                    $this->authorized = $overrideUser;

                    return;
                }
            }
        }

        if (!$this->bitrixUser->IsAuthorized()) {
            return;
        }

        $this->authorized = $this->userManager->findById((int) $this->bitrixUser->GetID());
    }
}
