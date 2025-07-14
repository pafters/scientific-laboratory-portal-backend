<?php

declare(strict_types=1);

namespace Phosagro\Event\Participatability\Errors;

use Phosagro\Event\Participatability\NotParticipatableReason;

final class EventIsRunningException extends EventAbstractException
{
    protected NotParticipatableReason $defaultReason = NotParticipatableReason::EVENT_IS_RUNNING;
}
