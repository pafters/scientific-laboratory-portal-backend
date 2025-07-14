<?php

declare(strict_types=1);

namespace Phosagro\System;

final class Clock
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
