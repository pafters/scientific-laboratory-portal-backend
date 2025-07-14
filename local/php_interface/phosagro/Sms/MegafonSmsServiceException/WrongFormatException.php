<?php

declare(strict_types=1);

namespace Phosagro\Sms\MegafonSmsServiceException;

use Phosagro\Util\Text;

final class WrongFormatException extends AbstractMegafonException
{
    public function __construct(string $error, string $body)
    {
        parent::__construct(GetMessage('MEGAFON_RESPONSE_WRONG_FORMAT', [
            '#ERROR#' => sprintf('[%s] %s', $error, Text::brief($body)),
        ]));
    }
}
