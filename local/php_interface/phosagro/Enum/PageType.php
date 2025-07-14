<?php

declare(strict_types=1);

namespace Phosagro\Enum;

use Phosagro\Util\Json;

enum PageType: string
{
    case COURSES = '#01HXYCMXT6WJ3V69K4TE575BP1#';
    case DETAIL = '#01HXYKW7KNYW89FJ6AB3SA47FS#';
    case EVENTS = '#01HXYCJ3GP6QVECCQJZ1C1H157#';
    case LIST = '#01HXYKWCDBGXNZ0BJNNMDFHD5R#';
    case NEWS = '#01HXYA926TXMW89E9WARBJZ2TQ#';
    case VIDEOS = '#01HXYCQJEPKJ27R9VYXPK6R4MY#';

    public function render(array $data = []): string
    {
        return $this->value.base64_encode(Json::encode($data)).$this->value;
    }

    public function toApi(array $data = []): array
    {
        return array_merge($data, match ($this) {
            self::COURSES => ['block' => 'courses'],
            self::DETAIL => ['block' => 'detail'],
            self::EVENTS => ['block' => 'events'],
            self::LIST => ['block' => 'list'],
            self::NEWS => ['block' => 'news'],
            self::VIDEOS => ['block' => 'videos'],
        });
    }
}
