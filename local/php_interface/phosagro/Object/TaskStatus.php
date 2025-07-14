<?php

declare(strict_types=1);

namespace Phosagro\Object;

enum TaskStatus: string
{
    case AVAILABLE = 'available';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case MODERATION = 'moderation';
    case UNAVAILABLE = 'unavailable';
}
