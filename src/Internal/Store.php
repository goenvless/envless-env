<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Store
{
    /** @var array<string, string> */
    private static array $values = [];

    /** @var array<string, array{type: string, visibility: string}> */
    private static array $meta = [];

    private static bool $loaded = false;

    private static bool $seeded = false;

    private static bool $injected = false;

    /**
     * @param array<string, string> $record
     * @param array<string, array{type: string, visibility: string}> $meta
     */
    public static function replace(array $record, array $meta): void
    {
        self::seed();
        self::$values = $record;
        self::$meta = [];

        foreach ($record as $name => $value) {
            self::$meta[$name] = $meta[$name] ?? Protocol::DEFAULT_VAR_META;
        }

        self::$loaded = true;
    }

    /** @return array<string, string> */
    public static function values(): array
    {
        self::seed();

        return self::$values;
    }

    /** @return array{type: string, visibility: string} */
    public static function meta(string $name): array
    {
        self::seed();

        return self::$meta[$name] ?? Protocol::DEFAULT_VAR_META;
    }

    public static function isLoaded(): bool
    {
        self::seed();

        return self::$loaded;
    }

    public static function isInjected(): bool
    {
        self::seed();

        return self::$injected;
    }

    private static function seed(): void
    {
        if (self::$seeded) {
            return;
        }

        self::$seeded = true;
        $seed = Seed::read();

        if ($seed !== null) {
            [self::$values, self::$meta] = $seed;
            self::$loaded = true;
            self::$injected = true;
        }
    }
}
