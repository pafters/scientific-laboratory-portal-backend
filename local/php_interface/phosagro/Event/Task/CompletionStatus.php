<?php

declare(strict_types=1);

namespace Phosagro\Event\Task;

enum CompletionStatus
{
    case ACCEPTED;
    case MODERATING;
    case REJECTED_BY_MODERATOR_ERROR;
    case REJECTED_BY_WRONG_ANSWERS;
    case REJECTED_BY_WRONG_FILES;
}
