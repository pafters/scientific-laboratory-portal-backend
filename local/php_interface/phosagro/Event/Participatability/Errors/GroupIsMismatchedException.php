<?php

declare(strict_types=1);

namespace Phosagro\Event\Participatability\Errors;

use Phosagro\Event\Participatability\NotParticipatableReason;

final class GroupIsMismatchedException extends ParticipatabilityException
{
    protected NotParticipatableReason $defaultReason = NotParticipatableReason::GROUP_IS_MISMATCHED;
}
