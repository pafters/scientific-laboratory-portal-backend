<?php

declare(strict_types=1);

namespace Phosagro\Enum;

enum LogEvent
{
    case ACCRUAL_REASON_MISSING;
    case ACCRUE_TASK_SCORE_FAILED;
    case MEGAFON_REQUEST_FAIL;
    case MEGAFON_REQUEST_SUCCESS;
    case MEGAFON_REQUEST_WRONG;
    case MEGAFON_SMS_NOT_FOUND;
    case MEGAFON_STATUS_UPDATED;
    case MEGAFON_STATUS_WRONG;
    case MUSEUM_DATABASE_WRONG;
    case MUSEUM_FILE_CLEANING_FAILED;
    case MUSEUM_SCORE_ACCRUAL_FAILED;
    case SUBSCRIPTION_SENDING_FAILED;
    case USER_EMAIL_CONFIRMED;
    case USER_PHONE_CONFIRMED;

    public function getBitrixSeverity(): string
    {
        return match ($this) {
            self::ACCRUAL_REASON_MISSING => 'WARNING',
            self::ACCRUE_TASK_SCORE_FAILED => 'ERROR',
            self::MEGAFON_REQUEST_FAIL => 'DEBUG',
            self::MEGAFON_REQUEST_SUCCESS => 'DEBUG',
            self::MEGAFON_REQUEST_WRONG => 'ERROR',
            self::MEGAFON_SMS_NOT_FOUND => 'WARNING',
            self::MEGAFON_STATUS_UPDATED => 'DEBUG',
            self::MEGAFON_STATUS_WRONG => 'WARNING',
            self::MUSEUM_DATABASE_WRONG => 'ERROR',
            self::MUSEUM_FILE_CLEANING_FAILED => 'ERROR',
            self::MUSEUM_SCORE_ACCRUAL_FAILED => 'ERROR',
            default => 'INFO',
        };
    }
}
