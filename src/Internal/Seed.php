<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Seed
{
    /** @return array{array<string, string>, array<string, array{type: string, visibility: string}>}|null */
    public static function read(): ?array
    {
        $raw = Environment::get(Protocol::INJECTED_MANIFEST['NAME']);
        $manifest = $raw === null || $raw === '' ? null : Json::object($raw);

        if ($manifest === null) {
            return null;
        }

        $record = [];
        $meta = [];

        foreach (get_object_vars($manifest) as $key => $entry) {
            $name = (string) $key;
            $value = Unsafe::is($name) ? null : Environment::get($name);

            if ($value !== null) {
                $record[$name] = $value;
                $meta[$name] = self::meta($entry);
            }
        }

        return $record === [] ? null : [$record, $meta];
    }

    /** @return array{type: string, visibility: string} */
    private static function meta(mixed $entry): array
    {
        $keys = Protocol::INJECTED_MANIFEST;
        $type = $entry instanceof \stdClass ? ($entry->{$keys['TYPE_KEY']} ?? null) : null;
        $visibility = $entry instanceof \stdClass ? ($entry->{$keys['VISIBILITY_KEY']} ?? null) : null;

        return [
            'type' => \is_string($type) && \in_array($type, Protocol::VAR_TYPES, true) ? $type : Protocol::DEFAULT_VAR_META['type'],
            'visibility' => \is_string($visibility) && \in_array($visibility, Protocol::VAR_VISIBILITY, true) ? $visibility : Protocol::DEFAULT_VAR_META['visibility'],
        ];
    }
}
