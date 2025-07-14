<?php

declare(strict_types=1);

namespace Phosagro\Event\Participatability\Errors;

use Phosagro\Event\Participatability\NotParticipatableReason;

final class UserIsAlreadyParticipantException extends UserAbstractException
{
    protected NotParticipatableReason $defaultReason = NotParticipatableReason::USER_IS_ALREADY_PARTICIPANT;
}
