<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Finalize
{
    /** @var array<string, true> */
    private static array $owned = [];

    /**
     * @param array<string, string> $record
     * @param list<Variable> $sources
     * @return array<string, string>
     */
    public static function run(array $record, array $sources): array
    {
        $meta = [];

        foreach ($sources as $source) {
            $meta[$source->name] = $source->meta();
        }

        Store::replace($record, $meta);

        $previous = self::$owned;
        $owned = [];
        $refused = [];

        foreach ($previous as $name => $true) {
            if (!\array_key_exists($name, $record)) {
                Environment::remove($name);
            }
        }

        foreach ($record as $name => $value) {
            if (Unsafe::is($name)) {
                $refused[] = $name;
            } elseif (!Environment::isSet($name) || isset($previous[$name])) {
                Environment::set($name, $value);
                $owned[$name] = true;
            }
        }

        if ($refused !== []) {
            Warn::emit(Protocol::UNSAFE_ENV_WARNING . implode(', ', $refused));
        }

        self::$owned = $owned;

        return $record;
    }
}
