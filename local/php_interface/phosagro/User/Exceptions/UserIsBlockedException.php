<?php

declare(strict_types=1);

namespace Phosagro\User\Exceptions;

final class UserIsBlockedException extends UserIsInactiveException
{
    public function __construct()
    {
        parent::__construct((string) GetMessage('LOGIN_BLOCKED'));
    }
}
