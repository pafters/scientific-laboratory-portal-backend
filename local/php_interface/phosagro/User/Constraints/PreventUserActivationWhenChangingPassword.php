<?php

declare(strict_types=1);

namespace Phosagro\User\Constraints;

final class PreventUserActivationWhenChangingPassword extends AbstractUserEditingListener
{
    private bool $isActivationPrevented = false;

    public function preventActivation(): void
    {
        $this->isActivationPrevented = true;
    }

    protected function execute(array &$fields): void
    {
        if ($this->isActivationPrevented) {
            unset($fields['ACTIVE']);
        }
    }
}
