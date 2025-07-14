<?php

declare(strict_types=1);

namespace Phosagro\Museum;

final class MuseumException extends \Exception
{
    public function __construct(
        string $message = '',
        public readonly string $item = '',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
