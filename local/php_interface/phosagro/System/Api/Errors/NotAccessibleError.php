<?php

declare(strict_types=1);

namespace Phosagro\System\Api\Errors;

use Phosagro\ApiError;

final class NotAccessibleError extends ApiError
{
    public function __construct()
    {
        parent::__construct(403, 'not_accessible');
    }
}
