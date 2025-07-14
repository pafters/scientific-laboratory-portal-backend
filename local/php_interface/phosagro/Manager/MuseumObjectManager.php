<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\Object\MuseumObject;
use Phosagro\Object\MuseumObjectStatus;

/**
 * @extends AbstractIblockElementManager<MuseumObject>
 */
final class MuseumObjectManager extends AbstractIblockElementManager
{
    /**
     * @return MuseumObject[]
     */
    public function getActiveMuseumObjects(): array
    {
        return $this->findAllElements([
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
        ]);
    }

    protected function createFromBitrixData(array $row): object
    {
        /** @var MuseumObjectStatus[] $statusList */
        $statusList = [];

        foreach ((array) ($row['PROPERTY_STATUS_VALUE'] ?? []) as $statusIndex => $statusValue) {
            $bonus = (int) ($row['PROPERTY_STATUS_DESCRIPTION'][$statusIndex] ?? 0);
            $status = trim((string) $statusValue);
            if ('' !== $status) {
                $statusList[] = new MuseumObjectStatus($bonus, $status);
            }
        }

        return new MuseumObject(
            (string) ($row['CODE'] ?? ''),
            (int) ($row['ID'] ?? 0),
            (string) ($row['NAME'] ?? ''),
            $statusList,
        );
    }

    protected function getBitrixFields(): array
    {
        return [
            'CODE',
            'IBLOCK_ID',
            'ID',
            'NAME',
            'PROPERTY_STATUS',
        ];
    }

    protected function getDefaultOrder(): array
    {
        return [
            'id' => 'asc',
            'sort' => 'asc',
        ];
    }
}
