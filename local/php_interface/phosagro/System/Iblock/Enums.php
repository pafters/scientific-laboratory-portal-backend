<?php

declare(strict_types=1);

namespace Phosagro\System\Iblock;

use Bitrix\Iblock\PropertyEnumerationTable;
use Phosagro\BitrixCache;
use Phosagro\System\Array\Accessor;

final class Enums
{
    private ?array $cache = null;

    public function getEnumId(int $proeprtyId, string $enum): int
    {
        return $this->getEnumIdMap()[$proeprtyId]["~{$enum}"];
    }

    /**
     * @return array<int,array<string,int>>
     */
    private function getEnumIdMap(): array
    {
        return $this->cache ??= $this->getEnumIdMapCache();
    }

    /**
     * @return array<int,array<string,int>>
     */
    private function getEnumIdMapCache(): array
    {
        return BitrixCache::get('/phosagro_enum_ids', $this->getEnumIdMapData(...));
    }

    /**
     * @return array<int,array<string,int>>
     */
    private function getEnumIdMapData(): array
    {
        /** @var array<int,array<string,int>> $result */
        $result = [];

        $found = PropertyEnumerationTable::getList([
            'select' => [
                'ID',
                'PROPERTY_ID',
                'XML_ID',
            ],
        ]);

        while ($row = $found->fetchRaw()) {
            $accessor = new Accessor($row);
            $propertyId = $accessor->getIntParsed('PROPERTY_ID');
            $codeKey = '~'.$accessor->getString('XML_ID');
            $result[$propertyId][$codeKey] = $accessor->getIntParsed('ID');
        }

        return $result;
    }
}
