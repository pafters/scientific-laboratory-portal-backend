<?php

declare(strict_types=1);

namespace Phosagro\Manager\Errors;

final class FoundMultipleException extends \Exception
{
    public function __construct(
        string $object,
        string $first,
        string $second,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(GetMessage($this::class, [
            '#OBJECT#' => $object,
            '#FIRST#' => $first,
            '#SECOND#' => $second,
        ]), $code, $previous);
    }
}
