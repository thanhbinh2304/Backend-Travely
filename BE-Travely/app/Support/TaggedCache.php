<?php

namespace App\Support;

use BadMethodCallException;
use Closure;
use Illuminate\Support\Facades\Cache;

class TaggedCache
{
    private static ?bool $supportsTags = null;

    public static function remember(array $tags, string $key, int $ttl, Closure $callback)
    {
        if (self::supportsTags()) {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }

        return Cache::remember(self::composeKey($tags, $key), $ttl, $callback);
    }

    public static function flush(array $tags): void
    {
        if (self::supportsTags()) {
            Cache::tags($tags)->flush();
            return;
        }

        $versionKey = self::versionKey($tags);
        $currentVersion = (int) Cache::get($versionKey, 1);
        Cache::forever($versionKey, $currentVersion + 1);
    }

    private static function supportsTags(): bool
    {
        if (self::$supportsTags !== null) {
            return self::$supportsTags;
        }

        try {
            Cache::tags(['tags-support-probe']);
            self::$supportsTags = true;
        } catch (BadMethodCallException $e) {
            self::$supportsTags = false;
        }

        return self::$supportsTags;
    }

    private static function composeKey(array $tags, string $key): string
    {
        $namespace = self::namespace($tags);
        $version = (int) Cache::get(self::versionKey($tags), 1);

        return 'tagged:' . $namespace . ':v' . $version . ':' . $key;
    }

    private static function versionKey(array $tags): string
    {
        return 'tagged:version:' . self::namespace($tags);
    }

    private static function namespace(array $tags): string
    {
        $normalized = array_values(array_unique($tags));
        sort($normalized);

        return md5(implode('|', $normalized));
    }
}
