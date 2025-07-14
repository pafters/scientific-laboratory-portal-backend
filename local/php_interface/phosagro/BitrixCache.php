<?php

declare(strict_types=1);

namespace Phosagro;

final class BitrixCache
{
    /** @var array<string,string> */
    private array $tags = [];

    private function __construct() {}

    public static function clearDir(string $path): void
    {
        [$cacheDir, $_] = self::getPathComponents($path);

        (new \CPHPCache())->CleanDir($cacheDir);
    }

    public static function clearKey(string $path): void
    {
        [$cacheDir, $cacheKey] = self::getPathComponents($path);
        (new \CPHPCache())->Clean($cacheKey, $cacheDir);
    }

    public static function clearTag(string $tag): void
    {
        global $CACHE_MANAGER;
        $CACHE_MANAGER->ClearByTag($tag);
    }

    public static function get(string $path, callable $callback, int $time = 86400): mixed
    {
        [$cacheDir, $cacheKey] = self::getPathComponents($path);

        $cache = new \CPHPCache();

        if ($cache->InitCache($time, $cacheKey, $cacheDir)) {
            $result = $cache->GetVars();

            return $result['RESULT'] ?? null;
        }

        $cache->StartDataCache();

        $cacheTags = new self();

        try {
            $data = $callback($cacheTags);
        } catch (\Throwable $exception) {
            $cache->AbortDataCache();

            throw $exception;
        }

        $tagList = $cacheTags->tags;

        if ([] !== $tagList) {
            global $CACHE_MANAGER;
            $CACHE_MANAGER->StartTagCache($cacheDir);
            foreach ($tagList as $tag) {
                $CACHE_MANAGER->RegisterTag($tag);
            }
            $CACHE_MANAGER->EndTagCache();
        }

        $cache->EndDataCache(['RESULT' => $data]);

        return $data;
    }

    public function tag(string $tag): void
    {
        $this->tags["~{$tag}"] = $tag;
    }

    /**
     * @return list{string,string}
     */
    private static function getPathComponents(string $path): array
    {
        $pathComponents = [];

        foreach (explode('/', $path) as $forwardSlashComponent) {
            foreach (explode('\\', $forwardSlashComponent) as $component) {
                $trimmed = trim($component);
                if ('' !== $trimmed) {
                    $pathComponents[] = $trimmed;
                }
            }
        }

        if ([] === $pathComponents) {
            throw new \InvalidArgumentException('Empty cache path.');
        }

        if (1 === \count($pathComponents)) {
            $pathComponents[] = $pathComponents[array_key_first($pathComponents)];
        }

        array_unshift($pathComponents, '');

        $suffix = array_pop($pathComponents);
        $prefix = implode('/', $pathComponents);

        return [$prefix, $suffix];
    }
}
