<?php

declare(strict_types=1);

namespace Phosagro\Util;

enum DateFormat: string
{
    case BITRIX = 'd.m.Y H:i:s';
    case BITRIX_DATE = 'd.m.Y';
    case DB = 'Y-m-d H:i:s';
    case DB_DATE = 'Y-m-d';
    case MUSEUM_DATE = 'j.n.Y';

    public function isTimeless(): bool
    {
        return match ($this) {
            self::BITRIX_DATE => true,
            self::DB_DATE => true,
            self::MUSEUM_DATE => true,
            default => false,
        };
    }
}
