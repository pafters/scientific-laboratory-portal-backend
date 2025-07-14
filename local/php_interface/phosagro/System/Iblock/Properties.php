<?php

declare(strict_types=1);

namespace Phosagro\System\Iblock;

use Bitrix\Iblock\PropertyTable;
use Phosagro\BitrixCache;
use Phosagro\System\Array\Accessor;

final class Properties
{
    private ?array $cache = null;

    public function __construct(
        private readonly Enums $enums,
    ) {}

    public function getEnumId(int $iblockId, string $property, string $enum): int
    {
        return $this->enums->getEnumId($this->getPropertyId($iblockId, $property), $enum);
    }

    public function getPropertyId(int $iblockId, string $property): int
    {
        return $this->getPropertyIdMap()[$iblockId]["~{$property}"];
    }

    /**
     * @return array<int,array<string,int>>
     */
    private function getPropertyIdMap(): array
    {
        return $this->cache ??= $this->getPropertyIdMapCache();
    }

    /**
     * @return array<int,array<string,int>>
     */
    private function getPropertyIdMapCache(): array
    {
        return BitrixCache::get('/phosagro_property_ids', $this->getPropertyIdMapData(...));
    }

    /**
     * @return array<int,array<string,int>>
     */
    private function getPropertyIdMapData(): array
    {
        /** @var array<int,array<string,int>> $result */
        $result = [];

        $found = PropertyTable::getList([
            'select' => [
                'CODE',
                'ID',
                'IBLOCK_ID',
            ],
        ]);

        while ($row = $found->fetchRaw()) {
            $accessor = new Accessor($row);
            $iblockId = $accessor->getIntParsed('IBLOCK_ID');
            $codeKey = '~'.$accessor->getString('CODE');
            $result[$iblockId][$codeKey] = $accessor->getIntParsed('ID');
        }

        return $result;
    }
}
