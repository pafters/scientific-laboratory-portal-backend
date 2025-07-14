<?php

declare(strict_types=1);

namespace Phosagro\Migration;

use Bitrix\Main\DB\Result as DbResult;
use Bitrix\Main\Result as MainResult;

use function Phosagro\get_bitrix_error;

final class DatabaseHelper
{
    public function assertSuccess($result, string $entity, string $key, string $action, string $error = ''): void
    {
        if ($result instanceof MainResult) {
            if (!$result->isSuccess()) {
                $error = implode(' ', $result->getErrorMessages());

                throw new \RuntimeException(sprintf('Can not %s %s "%s". %s', $action, $entity, $key, $error));
            }

            return;
        }

        if (!$result) {
            $error = (('' === $error) ? get_bitrix_error() : $error);

            throw new \RuntimeException(sprintf('Can not %s %s "%s". %s', $action, $entity, $key, $error));
        }
    }

    public function fetchSingle(DbResult $found, string $entity, string $key): array
    {
        $single = $found->fetchRaw();

        if (false === $single) {
            throw new \RuntimeException(sprintf('Not found %s "%s".', $entity, $key));
        }

        if (false !== $found->fetchRaw()) {
            throw new \RuntimeException(sprintf('Found more than one %s "%s".', $entity, $key));
        }

        return $single;
    }

    public function fetchSingleId(DbResult $found, string $entity, string $key): int
    {
        return (int) $this->fetchSingleInt($found, $entity, $key, 'ID');
    }

    public function fetchSingleInt(DbResult $found, string $entity, string $key, string $field): int
    {
        return (int) $this->fetchSingle($found, $entity, $key)[$field];
    }
}
