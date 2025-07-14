<?php

declare(strict_types=1);

namespace Phosagro\Object;

enum AccrualReasonCode: string
{
    case ADMIN_DECISION = 'admin_decision';
    case EVENT_COMPLETION = 'event_completion';
    case GAME_PLAY = 'game_play';
    case MOODLE_COMPLETION = 'moodle_completion';
    case MUSEUM_VISIT = 'museum_visit';
    case TASK_COMPLETION = 'task_completion';
}
