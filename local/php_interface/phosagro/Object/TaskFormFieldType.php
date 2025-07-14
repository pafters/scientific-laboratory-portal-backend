<?php

declare(strict_types=1);

namespace Phosagro\Object;

enum TaskFormFieldType
{
    case CHECKBOX;
    case NUMBER;
    case RADIO;
    case TEXT;

    public function hasVariants(): bool
    {
        return match ($this) {
            self::CHECKBOX => true,
            self::NUMBER => false,
            self::RADIO => true,
            self::TEXT => false,
        };
    }
}
