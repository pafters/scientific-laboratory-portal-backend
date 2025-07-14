<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\Object\MuseumVisit;
use Phosagro\Util\Date;
use Phosagro\Util\DateFormat;

/**
 * @extends AbstractHighloadblockManager<MuseumVisit>
 */
final class MuseumVisitManager extends AbstractHighloadblockManager
{
    /**
     * @return MuseumVisit[]
     */
    public function getAllVisits(): array
    {
        return $this->findAllElements();
    }

    /**
     * @return MuseumVisit[]
     */
    public function getNotAcruedVisits(): array
    {
        return $this->findAllElements([
            'filter' => [
                '!=UF_ACCRUED' => '1',
            ],
        ]);
    }

    protected function createFromBitrixData(array $row): object
    {
        return new MuseumVisit(
            '1' === trim((string) ($row['UF_ACCRUED'] ?? '')),
            Date::fromFormat(trim((string) ($row['UF_DATE'] ?? '')), DateFormat::BITRIX, DateFormat::BITRIX_DATE),
            (int) ($row['ID'] ?? 0),
            (int) (string) ($row['UF_OBJECT'] ?? ''),
            trim((string) ($row['UF_STATUS'] ?? '')),
            (int) ($row['UF_USER'] ?? 0),
            trim((string) ($row['UF_VISIT'] ?? '')),
        );
    }

    protected function getBitrixFields(): array
    {
        return [
            'ID',
            'UF_ACCRUED',
            'UF_DATE',
            'UF_OBJECT',
            'UF_STATUS',
            'UF_USER',
            'UF_VISIT',
        ];
    }
}
