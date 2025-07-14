<?php

declare(strict_types=1);

namespace Phosagro\Event\EventState;

use Phosagro\Object\EventState;

final class EventStateInfo
{
    public function __construct(
        public readonly ?\DateInterval $remaining,
        public readonly EventState $state,
    ) {}
}
