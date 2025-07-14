<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\Manager\Errors\FoundMultipleException;
use Phosagro\Manager\Errors\NotFoundException;
use Phosagro\Object\DatabaseChanges;
use Phosagro\System\Highloadblocks;

/**
 * @template T
 */
abstract class AbstractHighloadblockManager extends AbstractEntityManager
{
    public function __construct(
        private readonly Highloadblocks $highloadblocks,
    ) {}

    /**
     * @return T[]
     */
    public function findAllElements(array $parameters = []): array
    {
        /** @var T[] $result */
        $result = [];

        $found = $this->highloadblocks->findAllHighloadblockElements($this->getEntityName(), [
            'order' => $parameters['order'] ?? $this->getDefaultOrder(),
            'select' => $this->getBitrixFields(),
        ] + $parameters);

        foreach ($found as $row) {
            $result[] = $this->createFromBitrixData($row);
        }

        $this->loadElementList($result);

        return $result;
    }

    /**
     * @return ?T
     */
    public function findFirstElement(array $parameters = []): ?object
    {
        $found = $this->findAllElements(['limit' => 1] + $parameters);

        if ([] === $found) {
            return null;
        }

        return array_shift($found);
    }

    /**
     * @return ?T
     */
    public function findSingleElement(array $parameters = []): ?object
    {
        $found = $this->findAllElements(['limit' => 2] + $parameters);

        if ([] === $found) {
            return null;
        }

        $first = array_shift($found);

        if ([] !== $found) {
            $second = array_shift($found);

            throw new FoundMultipleException($this->getEntityName(), "{$first}", "{$second}");
        }

        return $first;
    }

    /**
     * @return T
     */
    public function getFirstElement(array $parameters = []): object
    {
        return $this->findFirstElement($parameters) ?? throw new NotFoundException($this->getEntityName());
    }

    /**
     * @return T
     */
    public function getSingleElement(array $parameters = []): object
    {
        return $this->findSingleElement($parameters) ?? throw new NotFoundException($this->getEntityName());
    }

    public function saveChanges(DatabaseChanges $changes): void
    {
        foreach ($changes->added as $addedData) {
            $this->highloadblocks->addHighloadblockElement(
                $this->getEntityName(),
                $addedData,
            );
        }

        foreach ($changes->changed as $changedIdentifier => $changedData) {
            $this->highloadblocks->updateHighloadblockElement(
                $this->getEntityName(),
                $changedIdentifier,
                $changedData
            );
        }

        foreach ($changes->deleted as $deletedIdentifier) {
            $this->highloadblocks->deleteHighloadblockElement(
                $this->getEntityName(),
                $deletedIdentifier,
            );
        }
    }

    abstract protected function createFromBitrixData(array $row): object;

    abstract protected function getBitrixFields(): array;

    protected function getDefaultOrder(): array
    {
        return ['ID' => 'ASC'];
    }

    /**
     * @param T[]
     */
    protected function loadElementList(array $elementList): void {}
}
