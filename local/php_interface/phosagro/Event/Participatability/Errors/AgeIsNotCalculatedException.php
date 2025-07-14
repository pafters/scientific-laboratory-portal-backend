<?php

declare(strict_types=1);

namespace Phosagro\Event\Participatability\Errors;

use Phosagro\Event\Participatability\NotParticipatableReason;

final class AgeIsNotCalculatedException extends UserAbstractException
{
    protected NotParticipatableReason $defaultReason = NotParticipatableReason::AGE_IS_NOT_CALCULATED;
}
