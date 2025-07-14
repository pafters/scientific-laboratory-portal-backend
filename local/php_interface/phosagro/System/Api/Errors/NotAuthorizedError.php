<?php

declare(strict_types=1);

namespace Phosagro\System\Api\Errors;

use Phosagro\ApiError;

final class NotAuthorizedError extends ApiError
{
    public function __construct()
    {
        parent::__construct(401, 'not_authoried');
    }
}
