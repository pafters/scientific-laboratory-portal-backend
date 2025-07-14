<?php

declare(strict_types=1);

namespace Phosagro\Object;

enum EventState
{
    case COMPLETED;
    case PREPARING;
    case RUNNING;
}
