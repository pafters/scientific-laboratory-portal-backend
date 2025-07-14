<?php

declare(strict_types=1);

namespace Phosagro\Object\Bitrix;

final class UserField
{
    public function __construct(
        public readonly string $code,
        public readonly string $entity,
    ) {}
}
