<?php

declare(strict_types=1);

namespace Phosagro\System\Api\Errors;

use Phosagro\Object\TaskStatusReason;

final class TaskUnavailableError extends BadRequestError
{
    public function __construct(?TaskStatusReason $reason = null)
    {
        $data = [];

        if (null !== $reason) {
            $data['reason'] = $reason->value;
        }

        parent::__construct('task_unavailable', $data);
    }
}
