<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

use Envless\Env\Exception\MissingVariableException;
use Envless\Env\Exception\NotLoadedException;
use Envless\Env\Exception\ServerOnlyException;

final class Reader
{
    private function __construct(private readonly bool $client) {}

    public static function server(): self
    {
        return new self(false);
    }

    public static function client(): self
    {
        return new self(true);
    }

    public function value(string $name): string|int|float|bool
    {
        return Coerce::by($this->raw($name), Store::meta($name)['type']);
    }

    public function optional(string $name): string|int|float|bool|null
    {
        $raw = $this->raw($name, true);

        return $raw === null ? null : Coerce::by($raw, Store::meta($name)['type']);
    }

    /** @return ($optional is true ? string|null : string) */
    public function raw(string $name, bool $optional = false): ?string
    {
        if (!Store::isLoaded() && Records::isEmpty()) {
            throw new NotLoadedException(Messages::NOT_LOADED);
        }

        if (!Records::has($name)) {
            return $optional ? null : throw new MissingVariableException($name);
        }

        if (!$this->readable($name)) {
            throw new ServerOnlyException($name);
        }

        return Records::raw($name) ?? '';
    }

    public function has(string $name): bool
    {
        return Records::has($name) && $this->readable($name);
    }

    /** @return list<string> */
    public function names(): array
    {
        return array_values(array_filter(Records::names(), $this->readable(...)));
    }

    /** @return array<string, string|int|float|bool> */
    public function snapshot(): array
    {
        $values = [];

        foreach ($this->names() as $name) {
            $values[$name] = $this->value($name);
        }

        return $values;
    }

    private function readable(string $name): bool
    {
        return !$this->client || Store::meta($name)['visibility'] === Protocol::VAR_VISIBILITY['CLIENT'];
    }
}
