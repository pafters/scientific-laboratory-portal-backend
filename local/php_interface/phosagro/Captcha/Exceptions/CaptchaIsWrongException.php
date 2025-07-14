<?php

declare(strict_types=1);

namespace Phosagro\Captcha\Exceptions;

final class CaptchaIsWrongException extends \Exception
{
    public function __construct()
    {
        parent::__construct((string) GetMessage('main_user_captcha_error'));
    }
}
