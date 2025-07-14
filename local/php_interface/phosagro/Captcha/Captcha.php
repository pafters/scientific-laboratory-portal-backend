<?php

declare(strict_types=1);

namespace Phosagro\Captcha;

final class Captcha
{
    public function __construct(
        public readonly string $sid,
        public readonly string $code = '',
    ) {}
}
