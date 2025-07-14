<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class TaskVideo
{
    public function __construct(
        public readonly string $videoFileUrl,
        public readonly string $videoHtml,
    ) {}
}
