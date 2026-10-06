<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class CacheFile
{
    public static function name(Target $target): string
    {
        $product = $target->product === null || $target->product === '' ? Protocol::CACHE['NO_PRODUCT'] : self::sanitize($target->product);
        $key = $product . '__' . self::sanitize($target->project) . '__' . self::sanitize($target->environment) . '.json';

        return $target->workspace === null || $target->workspace === '' ? $key : self::sanitize($target->workspace) . '__' . $key;
    }

    public static function sanitize(string $slug): string
    {
        $replaced = preg_replace_callback(Defaults::SLUG_UNSAFE . 'u', static fn(array $match): string => \strlen($match[0]) === 4 ? '__' : '_', $slug);

        return $replaced ?? (string) preg_replace(Defaults::SLUG_UNSAFE, '_', $slug);
    }

    public static function isFresh(string $cachedAt): bool
    {
        $stamped = Timestamp::parse($cachedAt);

        if ($stamped === null) {
            return false;
        }

        $age = Timestamp::milliseconds() - $stamped;

        return $age >= 0 && $age < Protocol::CACHE['MAX_AGE_MS'];
    }
}
