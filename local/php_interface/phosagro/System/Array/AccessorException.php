<?php

declare(strict_types=1);

namespace Phosagro\System\Array;

use Bitrix\Main\Localization\Loc;

abstract class AccessorException extends \Exception
{
    public function __construct(
        public readonly string $field,
    ) {
        parent::__construct(Loc::getMessage(static::class, ['#FIELD#' => $field]));
    }
}
