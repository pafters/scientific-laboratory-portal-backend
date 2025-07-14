<?php

declare(strict_types=1);

namespace Phosagro\User\Exceptions;

final class UserIsModeratingException extends UserIsInactiveException
{
    public function __construct()
    {
        parent::__construct((string) GetMessage('LOGIN_INACTIVE'));
    }
}
