<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Records
{
    public static function has(string $name): bool
    {
        return \array_key_exists($name, Store::values()) || Layers::has($name);
    }

    public static function raw(string $name): ?string
    {
        return Layers::raw($name) ?? Store::values()[$name] ?? null;
    }

    /** @return list<string> */
    public static function names(): array
    {
        return array_keys(array_replace(Store::values(), Layers::names()));
    }

    public static function isEmpty(): bool
    {
        return Store::values() === [] && Layers::names() === [];
    }

    /** @return array<string, string> */
    public static function snapshot(): array
    {
        $record = [];

        foreach (self::names() as $name) {
            $record[$name] = self::raw($name) ?? '';
        }

        return $record;
    }
}
