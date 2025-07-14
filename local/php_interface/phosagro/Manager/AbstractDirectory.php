<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\BitrixCache;

/**
 * @template T
 */
abstract class AbstractDirectory
{
    /** @var \WeakMap<object,int> */
    private \WeakMap $bitrixIdCache;

    private bool $cacheLoaded = false;

    /** @var array<int,object> */
    private array $instanceCache = [];

    /**
     * @return T[]
     */
    final public function findAll(): array
    {
        $this->tryLoadCached();

        return array_values($this->instanceCache);
    }

    final public function findBitrixId(object $object): ?int
    {
        $this->tryLoadCached();

        return $this->bitrixIdCache[$object] ?? null;
    }

    /**
     * @return ?T
     */
    final public function findOne(int $bitrixId): ?object
    {
        $this->tryLoadCached();

        return $this->instanceCache[$bitrixId] ?? null;
    }

    final protected function addItem(int $bitrixId, object $item): void
    {
        if (\array_key_exists($bitrixId, $this->instanceCache)) {
            throw new \RuntimeException(sprintf('Duplicate %s "%d".', static::class, $bitrixId));
        }

        $this->bitrixIdCache[$item] = $bitrixId;
        $this->instanceCache[$bitrixId] = $item;
    }

    abstract protected function createItem(array $row): void;

    abstract protected function loadDatabase(BitrixCache $cache): array;

    final protected function tryLoadCached(): void
    {
        try {
            $this->loadCached();
        } catch (\Throwable) {
            BitrixCache::clearDir(static::class);

            $this->loadCached();
        }
    }

    private function loadCached(): void
    {
        if ($this->cacheLoaded) {
            return;
        }

        $this->bitrixIdCache = new \WeakMap();
        $this->instanceCache = [];

        foreach (BitrixCache::get($this::class, $this->loadDatabase(...)) as $row) {
            $this->createItem($row);
        }

        $this->cacheLoaded = true;
    }
}
