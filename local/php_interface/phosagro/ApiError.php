<?php

declare(strict_types=1);

namespace Phosagro;

abstract class ApiError extends \Exception
{
    public function __construct(
        int $status = 500,
        string $error = 'unknown_error',
        public readonly array $data = [],
    ) {
        parent::__construct($error, $status);
    }
}
