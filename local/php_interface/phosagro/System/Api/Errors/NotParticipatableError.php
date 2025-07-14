<?php

declare(strict_types=1);

namespace Phosagro\System\Api\Errors;

use Phosagro\Event\Participatability\Errors\ParticipatabilityException;

final class NotParticipatableError extends BadRequestError
{
    public function __construct(ParticipatabilityException $error)
    {
        $data = [
            'message' => [GetMessage($error::class)],
            'reason' => $error->reason->name,
        ];

        if (null !== $error->field) {
            $data['field'] = $error->field->name;
        }

        ksort($data, SORT_STRING);

        parent::__construct('not_participatable', $data);
    }
}
