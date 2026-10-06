<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Layers
{
    /** @var list<array{int, array<string, string>}> */
    private static array $stack = [];

    private static int $counter = 0;

    /** @param array<string, string> $values */
    public static function push(array $values): int
    {
        self::$stack[] = [++self::$counter, $values];

        return self::$counter;
    }

    public static function pop(int $id): void
    {
        for ($index = \count(self::$stack) - 1; $index >= 0; $index--) {
            if (self::$stack[$index][0] === $id) {
                array_splice(self::$stack, $index, 1);

                return;
            }
        }
    }

    public static function has(string $name): bool
    {
        foreach (self::$stack as [, $values]) {
            if (\array_key_exists($name, $values)) {
                return true;
            }
        }

        return false;
    }

    public static function raw(string $name): ?string
    {
        for ($index = \count(self::$stack) - 1; $index >= 0; $index--) {
            if (\array_key_exists($name, self::$stack[$index][1])) {
                return self::$stack[$index][1][$name];
            }
        }

        return null;
    }

    /** @return array<string, true> */
    public static function names(): array
    {
        $names = [];

        foreach (self::$stack as [, $values]) {
            foreach ($values as $name => $value) {
                $names[$name] = true;
            }
        }

        return $names;
    }
}
