<?php

declare(strict_types=1);

namespace Phosagro\System\Api\Errors;

final class CaptchaRequiredError extends BadRequestError
{
    public function __construct()
    {
        parent::__construct('captcha_required');
    }
}
