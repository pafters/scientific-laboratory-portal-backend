<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\Iblocks;
use Phosagro\Manager\Errors\FoundMultipleException;
use Phosagro\Manager\Errors\NotFoundException;
use Phosagro\Util\Text;
use Stringable;

/**
 * @template T of Stringable
 */
abstract class AbstractIblockElementManager extends AbstractEntityManager
{
    public function deleteByIdentifier(int $identifier): void
    {
        // @var \CMain $APPLICATION

        global $APPLICATION;

        $APPLICATION->ResetException();

        $result = \CIBlockElement::Delete($identifier);

        if (!$result) {
            $error = $APPLICATION->GetException()->GetString();
            $error = (\is_string($error) ? trim($error) : get_debug_type($error));

            throw new \RuntimeException($error);
        }
    }

    /**
     * @return T[]
     */
    public function findAllElements(
        array $filter = [],
        array $order = [],
        array $nav = [],
    ): array {
        $result = [];

        $found = \CIBlockElement::GetList(
            $order + $this->getDefaultOrder(),
            $filter + ['IBLOCK_ID' => $this->getIblockId()],
            false,
            ([] === $nav) ? false : $nav,
            $this->getBitrixFields(),
        );

        while ($row = $found->Fetch()) {
            $result[] = $this->createFromBitrixData($row);
        }

        $this->loadElementList($result);

        return $result;
    }

    /**
     * @return ?T
     */
    public function findFirstElement(
        array $filter = [],
        array $order = [],
        array $nav = [],
    ): ?object {
        $found = $this->findAllElements($filter, $order, $nav);

        if ([] === $found) {
            return null;
        }

        return array_shift($found);
    }

    /**
     * @return ?T
     */
    public function findSingleElement(
        array $filter = [],
        array $order = [],
        array $nav = [],
    ): ?object {
        $found = $this->findAllElements($filter, $order, $nav);

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
    public function getFirstElement(
        array $filter = [],
        array $order = [],
        array $nav = [],
    ): object {
        return $this->findFirstElement($filter, $order, $nav) ?? throw new NotFoundException($this->getEntityName());
    }

    /**
     * @return T
     */
    public function getSingleElement(
        array $filter = [],
        array $order = [],
        array $nav = [],
    ): object {
        return $this->findSingleElement($filter, $order, $nav) ?? throw new NotFoundException($this->getEntityName());
    }

    abstract protected function createFromBitrixData(array $row): object;

    abstract protected function getBitrixFields(): array;

    protected function getDefaultOrder(): array
    {
        return ['id' => 'asc'];
    }

    protected function getIblockId(): int
    {
        try {
            return Iblocks::getIblockIdentifier($this->getEntityName());
        } catch (\Throwable) {
            throw new \LogicException(sprintf('Please override getIblockId method for "%s".', static::class));
        }
    }

    /**
     * @param T[]
     */
    protected function loadElementList(array $elementList): void {}
}
