<?php

declare(strict_types=1);

namespace Phosagro\Event\Participatability\Errors;

use Phosagro\Event\Participatability\NotParticipatableReason;

final class UserRefusedToParticipateException extends UserAbstractException
{
    protected NotParticipatableReason $defaultReason = NotParticipatableReason::USER_REFUSED_TO_PARTICIPATE;
}
