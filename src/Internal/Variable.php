<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Variable
{
    /** @param array<string, mixed> $meta */
    private function __construct(public readonly string $name, public readonly mixed $value, private readonly array $meta) {}

    public static function fromApi(mixed $item): ?self
    {
        if (!\is_array($item) || !\is_string($item['name'] ?? null)) {
            return null;
        }

        $meta = [];

        foreach (['type', 'visibility'] as $key) {
            if (\array_key_exists($key, $item)) {
                $meta[$key] = $item[$key];
            }
        }

        return new self($item['name'], $item['value'] ?? '', $meta);
    }

    public static function fromCache(mixed $entry): ?self
    {
        $name = $entry instanceof \stdClass ? ($entry->name ?? null) : null;
        $value = $entry instanceof \stdClass ? ($entry->value ?? null) : null;

        if (!\is_string($name) || !\is_string($value)) {
            return null;
        }

        $meta = [];

        foreach (['type', 'visibility'] as $key) {
            $field = $entry instanceof \stdClass ? ($entry->{$key} ?? null) : null;

            if (\is_string($field)) {
                $meta[$key] = $field;
            }
        }

        return new self($name, $value, $meta);
    }

    /** @return array{type: string, visibility: string} */
    public function meta(): array
    {
        $type = $this->meta['type'] ?? null;
        $visibility = $this->meta['visibility'] ?? null;

        return [
            'type' => \is_string($type) ? $type : Protocol::DEFAULT_VAR_META['type'],
            'visibility' => \is_string($visibility) ? $visibility : Protocol::DEFAULT_VAR_META['visibility'],
        ];
    }

    /** @return array<string, mixed> */
    public function cached(): array
    {
        return ['name' => $this->name, 'value' => $this->value, ...$this->meta];
    }
}
