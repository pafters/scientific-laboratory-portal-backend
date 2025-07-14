<?php

declare(strict_types=1);

namespace Phosagro\System\Api\Errors;

use Phosagro\ApiError;

class BadRequestError extends ApiError
{
    public function __construct(string $error, array $data = [])
    {
        parent::__construct(400, $error, $data);
    }
}
