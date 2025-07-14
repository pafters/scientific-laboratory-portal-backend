<?php

declare(strict_types=1);

namespace Phosagro\System;

use Bitrix\Highloadblock\HighloadBlockTable;
use Phosagro\BitrixCache;

final class Highloadblocks
{
    private ?array $cache = null;

    public function addHighloadblockElement(string $name, array $fields): void
    {
        $result = $this->getHighloadblock($name)::add($fields);

        if (!$result->isSuccess()) {
            throw new \RuntimeException(sprintf(
                'Can not add %s. %s',
                $name,
                implode(' ', $result->getErrorMessages()),
            ));
        }
    }

    public function deleteHighloadblockElement(string $name, int $identifier): void
    {
        $result = $this->getHighloadblock($name)::delete($identifier);

        if (!$result->isSuccess()) {
            throw new \RuntimeException(sprintf(
                'Can not delete %s %d. %s',
                $name,
                $identifier,
                implode(' ', $result->getErrorMessages()),
            ));
        }
    }

    public function findAllHighloadblockElements(string $name, array $parameters): \Generator
    {
        $found = $this->getHighloadblock($name)::getList($parameters);

        while ($row = $found->fetch()) {
            yield $row;
        }
    }

    public function findFirstHighloadblockElement(string $name, array $parameters): ?array
    {
        return $this->findAllHighloadblockElements($name, ['limit' => 1] + $parameters)->current() ?: null;
    }

    public function updateHighloadblockElement(string $name, int $identifier, array $fields): void
    {
        $result = $this->getHighloadblock($name)::update($identifier, $fields);

        if (!$result->isSuccess()) {
            throw new \RuntimeException(sprintf(
                'Can not update %s %d. %s',
                $name,
                $identifier,
                implode(' ', $result->getErrorMessages()),
            ));
        }
    }

    private function getHighloadblock(string $name): string
    {
        return HighloadBlockTable::compileEntity($this->getHighloadblockMap()["~{$name}"])->getDataClass();
    }

    private function getHighloadblockMap(): array
    {
        return $this->cache ??= BitrixCache::get('phosagro_highloadblocks', $this->loadHighloadblockMap(...));
    }

    private function loadHighloadblockMap(): array
    {
        $result = [];

        $found = HighloadBlockTable::getList();

        while ($row = $found->fetch()) {
            $result['~'.$row['NAME']] = $row;
        }

        return $result;
    }
}
