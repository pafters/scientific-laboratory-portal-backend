<?php

declare(strict_types=1);

namespace Phosagro\Object;

enum TaskStatusReason: string
{
    case ALL_GOOD = 'all_good';
    case NOT_A_PARTICIPANT = 'not_a_participant';
    case NOT_COMPLETED_PREVIOUS_REQUIRED = 'not_completed_previous_required';
    case PARTICIPANT_REFUSED = 'participant_refused';
    case TASK_FILES_IS_REJECTED = 'task_files_is_rejected';
    case TASK_FORM_IS_MISSING = 'task_form_is_missing';
    case TASK_FORM_IS_WRONG = 'task_form_is_wrong';
    case TASK_IS_CLOSED = 'task_is_closed';
    case TASK_IS_COMPLETED = 'task_is_completed';
    case TASK_IS_ENDED = 'task_is_ended';
    case TASK_IS_NOT_STARTED = 'task_is_not_started';
    case TASK_IS_ON_MODERATION = 'task_is_on_moderation';

    public function getStatus(): TaskStatus
    {
        return match ($this) {
            self::ALL_GOOD => TaskStatus::AVAILABLE,
            self::NOT_A_PARTICIPANT => TaskStatus::UNAVAILABLE,
            self::NOT_COMPLETED_PREVIOUS_REQUIRED => TaskStatus::UNAVAILABLE,
            self::PARTICIPANT_REFUSED => TaskStatus::UNAVAILABLE,
            self::TASK_FILES_IS_REJECTED => TaskStatus::FAILED,
            self::TASK_FORM_IS_MISSING => TaskStatus::FAILED,
            self::TASK_FORM_IS_WRONG => TaskStatus::FAILED,
            self::TASK_IS_CLOSED => TaskStatus::UNAVAILABLE,
            self::TASK_IS_COMPLETED => TaskStatus::COMPLETED,
            self::TASK_IS_ENDED => TaskStatus::UNAVAILABLE,
            self::TASK_IS_NOT_STARTED => TaskStatus::UNAVAILABLE,
            self::TASK_IS_ON_MODERATION => TaskStatus::MODERATION,
        };
    }
}
