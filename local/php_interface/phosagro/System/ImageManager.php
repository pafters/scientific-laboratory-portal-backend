<?php

declare(strict_types=1);

namespace Phosagro\System;

use Phosagro\System\UrlManager;

final class ImageManager
{
    private $types;
    public function __construct(private readonly UrlManager $urlManager)
    {
        $this->types = [
            'news' => [
                'small_item' => [
                    'width' => 270,
                    'height' => 200
                ],
                'big_item' => [

                ],
                'main' => [
                    'width' => 1920,
                    'height' => 713
                ]
            ],
            'events' => [
                'small_item' => [
                    'width' => 580,
                    'height' => 429
                ],
                'big_item' => [
                    'width' => 580,
                    'height' => 639
                ],
                'main' => [
                    'width' => 1920,
                    'height' => 713
                ]
            ],
            'courses' => [
                'small_item' => [
                    'width' => 580,
                    'height' => 429
                ],
                'big_item' => [
                    'width' => 580,
                    'height' => 639
                ],
                'main' => [
                    'width' => 1920,
                    'height' => 713
                ]
            ],
            'detail' => [
                'width' => 1200,
                'height' => 600
            ],
            'gallery' => [
                'small_item' => [
                    'width' => 162,
                    'height' => 124
                ]
            ],
            'contacts' => [
                'small_item' => [
                    'width' => 273,
                    'height' => 145
                ]
            ],
            'none' => [
                'width' => null,
                'height' => null
            ],
            'voting' => [
                'detail' => [
                    'height' => 200,
                    'width' => 200,
                ],
                'thumbnail' => [
                    'height' => 100,
                    'width' => 100,
                ],
            ],
        ];
    }

    public function resizeImage(string|int|null $imageId, string $type, string|null $subtype = null): string
    {
        $thumb = \CFile::ResizeImageGet(
            $imageId,
            [
                'width' => $subtype ? $this->types[$type][$subtype]['width'] : $this->types[$type]['width'],
                'height' => $subtype ? $this->types[$type][$subtype]['height'] : $this->types[$type]['width'],
            ],
            BX_RESIZE_IMAGE_EXACT,
            true,
            []
        );
        $thumbSrc = (is_array($thumb) ? ($thumb['src'] ?? '') : '');

        return $this->urlManager->makeAbsolute($thumbSrc);

    }
}
