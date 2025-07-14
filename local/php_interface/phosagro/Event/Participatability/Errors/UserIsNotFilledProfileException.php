<?php

declare(strict_types=1);

namespace Phosagro\Event\Participatability\Errors;

use Phosagro\Enum\UserField;
use Phosagro\Event\Participatability\NotParticipatableReason;
use Phosagro\Object\Bitrix\User;
use Phosagro\Object\Event;

final class UserIsNotFilledProfileException extends UserAbstractException
{
    protected NotParticipatableReason $defaultReason = NotParticipatableReason::USER_IS_NOT_FILLED_PROFILE;

    public function __construct(
        Event $event,
        User $user,
        UserField $field,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $this->defaultField = $field;
        parent::__construct($event, $user, $message, $code, $previous);
    }
}
