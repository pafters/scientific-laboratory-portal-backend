<?php

declare(strict_types=1);

namespace Phosagro\Migration;

final class IblockElementHelper
{
    private readonly \CIBlockElement $manager;

    public function __construct(
        private readonly DatabaseHelper $database,
        private readonly IblockHelper $iblocks,
    ) {
        $this->manager = new \CIBlockElement();
    }

    public function createElement(string $type, string $iblock, array $fields): void
    {
        $actualFields = ['IBLOCK_ID' => $this->iblocks->getIblockId($type, $iblock)] + $fields;

        $this->database->assertSuccess(
            $this->manager->Add($actualFields),
            'iblock element',
            "{$type}/{$iblock}",
            'create',
            $this->manager->LAST_ERROR,
        );
    }

    public function deleteElement(string $type, string $iblock, array $filter): void
    {
        $this->database->assertSuccess(
            $this->manager->Delete($this->getElementIdentifier($type, $iblock, $filter)),
            'iblock element',
            "{$type}/{$iblock}",
            'delete',
            $this->manager->LAST_ERROR,
        );
    }

    public function getElementIdentifier(string $type, string $iblock, array $filter): int
    {
        $actualFilter = ['IBLOCK_ID' => $this->iblocks->getIblockId($type, $iblock)] + $filter;

        $found = \CIBlockElement::GetList(
            ['id' => 'asc'],
            $actualFilter,
            false,
            false,
            ['ID']
        );

        $first = $found->Fetch();

        if (!$first) {
            throw new \RuntimeException('Not found iblock element');
        }

        if ($found->Fetch()) {
            throw new \RuntimeException('Found multiple iblock elements.');
        }

        return (int) $first['ID'];
    }

    public function updateElement(string $type, string $iblock, array $filter, array $fields): void
    {
        $this->database->assertSuccess(
            $this->manager->Update($this->getElementIdentifier($type, $iblock, $filter), $fields),
            'iblock element',
            "{$type}/{$iblock}",
            'update',
            $this->manager->LAST_ERROR,
        );
    }
}
