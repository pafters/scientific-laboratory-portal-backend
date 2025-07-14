<?php

declare(strict_types=1);

namespace Phosagro\System\Api\Errors;

use Phosagro\ApiError;

final class ServerError extends ApiError
{
    public function __construct(array $message = [], array $data = [])
    {
        parent::__construct(500, 'server_error', ['message' => $message] + $data);
    }
}
