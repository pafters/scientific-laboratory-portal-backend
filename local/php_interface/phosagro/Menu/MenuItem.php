<?php

declare(strict_types=1);

namespace Phosagro\Menu;

final class MenuItem
{
    /** @var self[] */
    public array $children = [];

    public function __construct(
        public readonly string $title = '',
        public readonly string $url = '/',
    ) {}

    public function toApi(): array
    {
        return [
            'children' => array_map(static fn (self $item): array => $item->toApi(), $this->children),
            'title' => $this->title,
            'url' => $this->url,
        ];
    }
}
