<?php

declare(strict_types=1);

namespace Phosagro\Util;

final class Collection
{
    /**
     * @param array<int,null|int> $list
     *
     * @return int[]
     */
    public static function identifierList(array $list): array
    {
        $result = array_filter($list, '\is_int');
        $result = array_values($result);

        sort($result, SORT_NUMERIC);

        return $result;
    }
}
