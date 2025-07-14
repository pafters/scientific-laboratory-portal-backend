<?php

declare(strict_types=1);

namespace Phosagro\Object\Bitrix;

final class UserFieldEnum
{
    public function __construct(
        public readonly string $code,
        public readonly UserField $field,
        public readonly string $value,
    ) {}
}
