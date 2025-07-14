<?php

declare(strict_types=1);

namespace Phosagro\System\Api;

enum Error
{
    case BLOCKED;
    case DUPLICATE;
    case EXCEEDED;
    case INACTIVE;
    case INVALID;
    case REQUIRED;
    case REQUIRED_ANY;
    case REQUIRED_ONE;
    case UNEXPECTED;
}
