<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\BitrixCache;
use Phosagro\Iblocks;
use Phosagro\Object\AgeCategory;

/**
 * @method AgeCategory[] findAll()
 * @method ?AgeCategory  findOne(int $bitrixId)
 */
final class AgeCategoryManager extends AbstractDirectory
{
    /**
     * @return AgeCategory[]
     */
    public function findByAge(int $age): array
    {
        /** @var AgeCategory[] $result */
        $result = [];

        foreach ($this->findAll() as $ageCategory) {
            if ($age < $ageCategory->minimalAge) {
                continue;
            }

            if ((null !== $ageCategory->maximalAge) && ($ageCategory->maximalAge < $age)) {
                continue;
            }

            $result[] = $ageCategory;
        }

        return $result;
    }

    protected function createItem(array $row): void
    {
        $maximalAge = ($row['PROPERTY_MAXIMAL_AGE_VALUE'] ?? null);

        $item = new AgeCategory(
            (int) $row['ID'],
            (null === $maximalAge) ? null : (int) $maximalAge,
            (int) $row['PROPERTY_MINIMAL_AGE_VALUE'],
            $row['NAME'],
            (int) $row['PROPERTY_OWNER_VALUE'],
        );

        $this->addItem((int) $row['ID'], $item);
    }

    /**
     * @return AgeCategory[]
     */
    protected function loadDatabase(BitrixCache $cache): array
    {
        /** @var array[] $result */
        $result = [];

        $found = \CIBlockElement::GetList(
            [
                'sort' => 'asc',
                'property_MINIMAL_AGE' => 'asc',
                'property_MAXIMAL_AGE' => 'asc',
                'id' => 'asc',
            ],
            [
                'IBLOCK_ID' => Iblocks::ageCategoryId(),
            ],
            false,
            false,
            [
                'ID',
                'NAME',
                'PROPERTY_MAXIMAL_AGE',
                'PROPERTY_MINIMAL_AGE',
                'PROPERTY_OWNER',
            ],
        );

        while ($row = $found->Fetch()) {
            $result[] = $row;
        }

        $cache->tag(sprintf('iblock_id_%d', Iblocks::ageCategoryId()));

        return $result;
    }
}
