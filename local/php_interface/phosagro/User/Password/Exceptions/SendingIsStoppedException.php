<?php

declare(strict_types=1);

namespace Phosagro\User\Password\Exceptions;

final class SendingIsStoppedException extends \Exception
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: (string) GetMessage('SENDING_STOPPED'));
    }
}
