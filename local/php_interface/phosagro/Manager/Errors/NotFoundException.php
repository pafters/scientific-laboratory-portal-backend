<?php

declare(strict_types=1);

namespace Phosagro\Manager\Errors;

final class NotFoundException extends \Exception
{
    public function __construct(string $entity, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(GetMessage('NOT_FOUND_ENTITY', ['#ENTITY#' => $entity]), $code, $previous);
    }
}
