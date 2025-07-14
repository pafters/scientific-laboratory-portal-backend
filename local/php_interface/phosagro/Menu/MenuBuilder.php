<?php

declare(strict_types=1);

namespace Phosagro\Menu;

use Phosagro\System\Array\Accessor;

final class MenuBuilder
{
    public function createFromBitrixData(array $data): MenuItem
    {
        $root = new MenuItem(url: '/');

        /** @var array<int,self> $levels */
        $levels = [$root];

        foreach ($data as $row) {
            $accessor = new Accessor($row);

            $item = new MenuItem(
                $accessor->getStringTrimmed('TEXT'),
                $accessor->getStringTrimmed('LINK'),
            );

            $depth = $accessor->getInt('DEPTH_LEVEL');
            $levels[$depth] = $item;
            $levels[$depth - 1]->children[] = $item;
        }

        return $root;
    }
}
