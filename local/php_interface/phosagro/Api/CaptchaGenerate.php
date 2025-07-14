<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\System\Api\Route;
use Phosagro\System\UrlManager;

final class CaptchaGenerate
{
    public function __construct(
        private readonly \CMain $bitrix,
        private readonly UrlManager $urlManager,
    ) {}

    #[Route(pattern: '~^/api/captcha/generate/$~')]
    public function execute(): array
    {
        $sid = (string) $this->bitrix->CaptchaGetCode();
        $url = '/bitrix/tools/captcha.php?'.http_build_query(['captcha_code' => $sid]);

        return [
            'id' => $sid,
            'url' => $this->urlManager->makeAbsolute($url),
        ];
    }
}
