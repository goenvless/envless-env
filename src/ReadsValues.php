<?php

declare(strict_types=1);

namespace Envless\Env;

use Envless\Env\Exception\ReadOnlyException;
use Envless\Env\Internal\Coerce;
use Envless\Env\Internal\Messages;
use Envless\Env\Internal\Protocol;
use Envless\Env\Internal\Reader;

trait ReadsValues
{
    abstract private function reader(): Reader;

    public function get(string $name): string|int|float|bool
    {
        return $this->reader()->value($name);
    }

    public function optional(string $name): string|int|float|bool|null
    {
        return $this->reader()->optional($name);
    }

    public function string(string $name): string
    {
        return $this->reader()->raw($name);
    }

    public function number(string $name): int|float
    {
        return Coerce::number($this->reader()->raw($name));
    }

    public function boolean(string $name): bool
    {
        return Coerce::boolean($this->reader()->raw($name));
    }

    public function has(string $name): bool
    {
        return $this->reader()->has($name);
    }

    /** @return list<string> */
    public function names(): array
    {
        return $this->reader()->names();
    }

    public function __get(string $name): string|int|float|bool
    {
        return $this->reader()->value($name);
    }

    public function __isset(string $name): bool
    {
        return $this->reader()->has($name);
    }

    public function __set(string $name, mixed $value): void
    {
        throw new ReadOnlyException(Protocol::ENV_READ_ONLY . Messages::READ_ONLY_OVERRIDE);
    }

    public function __unset(string $name): void
    {
        throw new ReadOnlyException(Protocol::ENV_READ_ONLY);
    }

    public function offsetExists(mixed $offset): bool
    {
        return \is_string($offset) && $this->reader()->has($offset);
    }

    public function offsetGet(mixed $offset): string|int|float|bool
    {
        return $this->reader()->value(\is_string($offset) ? $offset : (\is_int($offset) ? (string) $offset : ''));
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new ReadOnlyException(Protocol::ENV_READ_ONLY . Messages::READ_ONLY_OVERRIDE);
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new ReadOnlyException(Protocol::ENV_READ_ONLY);
    }
}
