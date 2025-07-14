<?php

declare(strict_types=1);

namespace Phosagro\Event\Participatability\Errors;

use Phosagro\Enum\UserField;
use Phosagro\Event\Participatability\NotParticipatableReason;
use Phosagro\Object\Bitrix\User;
use Phosagro\Object\Event;

abstract class ParticipatabilityException extends \Exception
{
    public readonly ?UserField $field;
    public readonly NotParticipatableReason $reason;

    protected ?UserField $defaultField = null;
    protected NotParticipatableReason $defaultReason;

    public function __construct(
        public readonly Event $event,
        public readonly User $user,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
        $this->field = $this->defaultField;
        $this->reason = $this->defaultReason;
    }
}
