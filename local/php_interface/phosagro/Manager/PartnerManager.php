<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\BitrixCache;
use Phosagro\Iblocks;
use Phosagro\Object\Partner;

/**
 * @method Partner[] findAll()
 * @method ?Partner  findOne(int $bitrixId)
 */
final class PartnerManager extends AbstractDirectory
{
    protected function createItem(array $row): void
    {
        $this->addItem((int) $row['ID'], new Partner(
            sprintf('%d', $row['ID']),
            trim((string) $row['PROPERTY_COLOR_VALUE']),
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
                'IBLOCK_ID' => Iblocks::partnerId(),
            ],
            false,
            false,
            [
                'ID',
                'NAME',
                'PROPERTY_COLOR',
                'PROPERTY_OWNER',
            ],
        );

        while ($row = $found->Fetch()) {
            $result[] = $row;
        }

        $cache->tag(sprintf('iblock_id_%d', Iblocks::partnerId()));

        return $result;
    }
}
