<?php

declare(strict_types=1);

namespace Phosagro\Event\Participatability\Errors;

use Phosagro\Event\Participatability\NotParticipatableReason;

final class UserIsNotConfirmedPhoneException extends UserAbstractException
{
    protected NotParticipatableReason $defaultReason = NotParticipatableReason::USER_IS_NOT_CONFIRMED_PHONE;
}
