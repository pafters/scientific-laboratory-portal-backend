<?php

declare(strict_types=1);

namespace Phosagro\System\Api\Errors;

use Phosagro\ApiError;

final class NotFoundError extends ApiError
{
    public function __construct()
    {
        parent::__construct(404, 'not_found');
    }
}
