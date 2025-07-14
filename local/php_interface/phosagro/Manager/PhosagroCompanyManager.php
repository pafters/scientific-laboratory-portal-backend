<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\BitrixCache;
use Phosagro\Iblocks;
use Phosagro\Object\PhosagroCompany;

/**
 * @method PhosagroCompany[] findAll()
 * @method ?PhosagroCompany  findOne(int $bitrixId)
 */
final class PhosagroCompanyManager extends AbstractDirectory
{
    protected function createItem(array $row): void
    {
        $this->addItem((int) $row['ID'], new PhosagroCompany(
            (int) $row['ID'],
            $row['NAME'],
            (int) $row['PROPERTY_OWNER_VALUE'],
        ));
    }

    protected function loadDatabase(BitrixCache $cache): array
    {
        /** @var array[] $result */
        $result = [];

        $found = \CIBlockElement::GetList(
            [
                'name' => 'asc',
            ],
            [
                'IBLOCK_ID' => Iblocks::phosagroCompanyId(),
            ],
            false,
            false,
            [
                'ID',
                'NAME',
                'PROPERTY_OWNER',
            ],
        );

        while ($row = $found->Fetch()) {
            $result[] = $row;
        }

        $cache->tag(sprintf('iblock_id_%d', Iblocks::phosagroCompanyId()));

        return $result;
    }
}
