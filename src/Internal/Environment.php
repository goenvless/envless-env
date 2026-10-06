<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Environment
{
    private const REQUEST_HEADER = 'HTTP_';

    public static function get(string $name): ?string
    {
        foreach (self::sources($name) as $source) {
            if (\array_key_exists($name, $source) && \is_string($source[$name])) {
                return $source[$name];
            }
        }

        $value = self::settable($name) ? getenv($name, str_starts_with($name, self::REQUEST_HEADER)) : false;

        return \is_string($value) ? $value : null;
    }

    public static function filled(string $name): ?string
    {
        $value = self::get($name);

        return $value === null || $value === '' ? null : $value;
    }

    public static function isSet(string $name): bool
    {
        foreach (self::sources($name) as $source) {
            if (\array_key_exists($name, $source)) {
                return true;
            }
        }

        return self::settable($name) && getenv($name, str_starts_with($name, self::REQUEST_HEADER)) !== false;
    }

    public static function set(string $name, string $value): void
    {
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;

        if (self::settable($name) && !str_contains($value, "\0")) {
            putenv($name . '=' . $value);
        }
    }

    public static function remove(string $name): void
    {
        unset($_ENV[$name], $_SERVER[$name]);

        if (self::settable($name)) {
            putenv($name);
        }
    }

    /** @return list<array<mixed>> */
    private static function sources(string $name): array
    {
        return str_starts_with($name, self::REQUEST_HEADER) ? [$_ENV] : [$_ENV, $_SERVER];
    }

    private static function settable(string $name): bool
    {
        return $name !== '' && strpbrk($name, "=\0") === false;
    }
}
