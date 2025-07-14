<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\Util\Text;

abstract class AbstractEntityManager
{
    protected function getEntityClass(): string
    {
        $class = static::class;

        $suffix = 'Manager';

        if (!str_ends_with($class, $suffix)) {
            throw new \LogicException(sprintf('CPlease override getEntityClass method for "%s".', static::class));
        }

        $class = Text::substring($class, 0, Text::length($class) - Text::length($suffix));

        $prefix = 'Phosagro\Manager\\';

        if (!str_starts_with($class, $prefix)) {
            throw new \LogicException(sprintf('Please override getEntityClass method for "%s".', static::class));
        }

        $class = 'Phosagro\Object\\'.Text::substring($class, Text::length($prefix));

        return $class;
    }

    protected function getEntityName(): string
    {
        $class = $this->getEntityClass();

        $prefix = 'Phosagro\Object\\';

        if (!str_starts_with($class, $prefix)) {
            return $class;
        }

        return Text::substring($class, Text::length($prefix));
    }
}
