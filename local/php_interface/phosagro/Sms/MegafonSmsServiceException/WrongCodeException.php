<?php

declare(strict_types=1);

namespace Phosagro\Sms\MegafonSmsServiceException;

use Phosagro\Util\Text;

final class WrongCodeException extends AbstractMegafonException
{
    public function __construct(string $body, int $error, string $text)
    {
        parent::__construct(GetMessage('MEGAFON_RESPONSE_WRONG_CODE', [
            '#ERROR#' => sprintf('[%d] "%s" %s', $error, $text, Text::brief($body)),
        ]));
    }
}
