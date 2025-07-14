<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\BitrixCache;
use Phosagro\Iblocks;
use Phosagro\Object\City;

/**
 * @method City[] findAll()
 * @method ?City  findOne(int $bitrixId)
 */
final class CityManager extends AbstractDirectory
{
    /** @var array<string,City> */
    private array $byCode = [];

    public function findByCode(string $code): ?City
    {
        $this->tryLoadCached();

        return $this->byCode["~{$code}"] ?? null;
    }

    protected function createItem(array $row): void
    {
        $item = new City(
            $row['CODE'],
            $row['NAME'],
            (int) $row['PROPERTY_OWNER_VALUE'],
        );

        $key = "~{$item->code}";

        if (\array_key_exists($key, $this->byCode)) {
            throw new \RuntimeException(sprintf('Duplicate %s "%s".', $item::class, $item->code));
        }

        $this->byCode[$key] = $item;

        $this->addItem((int) $row['ID'], $item);
    }

    /**
     * @return City[]
     */
    protected function loadDatabase(BitrixCache $cache): array
    {
        /** @var array[] $result */
        $result = [];

        $found = \CIBlockElement::GetList(
            [
                'sort' => 'asc',
                'name' => 'asc',
            ],
            [
                'IBLOCK_ID' => Iblocks::cityId(),
            ],
            false,
            false,
            [
                'CODE',
                'ID',
                'NAME',
                'PROPERTY_OWNER',
            ],
        );

        while ($row = $found->Fetch()) {
            $result[] = $row;
        }

        $cache->tag(sprintf('iblock_id_%d', Iblocks::cityId()));

        return $result;
    }
}
