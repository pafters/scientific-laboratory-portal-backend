<?php

declare(strict_types=1);

namespace Phosagro\System\Api\Errors;

final class CodeRequiredError extends BadRequestError
{
    public function __construct()
    {
        parent::__construct('code_required');
    }
}
