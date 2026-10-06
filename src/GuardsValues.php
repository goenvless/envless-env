<?php

declare(strict_types=1);

namespace Envless\Env;

use Envless\Env\Exception\SerialisationException;
use Envless\Env\Internal\Messages;
use Envless\Env\Internal\Reader;

trait GuardsValues
{
    abstract private function reader(): Reader;

    /** @return \Generator<string, string|int|float|bool, mixed, void> */
    public function getIterator(): \Generator
    {
        foreach ($this->reader()->names() as $name) {
            yield $name => $this->reader()->value($name);
        }
    }

    public function count(): int
    {
        return \count($this->reader()->names());
    }

    /** @return array<string, mixed> */
    public function __serialize(): array
    {
        throw new SerialisationException(Messages::NOT_SERIALISABLE);
    }

    /** @param array<string, mixed> $data */
    public function __unserialize(array $data): void
    {
        throw new SerialisationException(Messages::NOT_SERIALISABLE);
    }
}
