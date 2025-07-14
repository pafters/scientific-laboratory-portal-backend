<?php

declare(strict_types=1);

namespace Phosagro\Sms\MegafonSmsServiceException;

use Phosagro\System\Array\AccessorException;
use Phosagro\Util\Text;

final class WrongFieldsException extends AbstractMegafonException
{
    public function __construct(string $body, AccessorException $previous)
    {
        parent::__construct(GetMessage('MEGAFON_RESPONSE_WRONG_FIELDS', [
            '#ERROR#' => sprintf('[%s] "%s" %s', $previous->field, $previous->getMessage(), Text::brief($body)),
        ]));
    }
}
