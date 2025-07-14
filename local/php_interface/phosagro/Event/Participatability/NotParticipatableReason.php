<?php

declare(strict_types=1);

namespace Phosagro\Event\Participatability;

enum NotParticipatableReason
{
    case AGE_IS_MISMATCHED;
    case AGE_IS_NOT_CALCULATED;
    case EVENT_IS_ARCHIVED;
    case EVENT_IS_COMPLETED;
    case EVENT_IS_NOT_PUBLISHED;
    case EVENT_IS_RUNNING;
    case GROUP_IS_MISMATCHED;
    case USER_IS_ALREADY_PARTICIPANT;
    case USER_IS_BLOCKED;
    case USER_IS_NOT_CONFIRMED_EMAIL;
    case USER_IS_NOT_CONFIRMED_PHONE;
    case USER_IS_NOT_FILLED_PROFILE;
    case USER_IS_NOT_MODERATED;
    case USER_REFUSED_TO_PARTICIPATE;
}
