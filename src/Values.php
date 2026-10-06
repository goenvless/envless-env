<?php

declare(strict_types=1);

namespace Envless\Env;

use Envless\Env\Exception\SerialisationException;
use Envless\Env\Internal\Messages;
use Envless\Env\Internal\Reader;

/**
 * @implements \ArrayAccess<string, string|int|float|bool>
 * @implements \IteratorAggregate<string, string|int|float|bool>
 */
final class Values implements \ArrayAccess, \IteratorAggregate, \Countable, \JsonSerializable
{
    use GuardsValues;
    use ReadsValues;

    public function jsonSerialize(): mixed
    {
        throw new SerialisationException(Messages::NOT_SERIALISABLE);
    }

    /** @return array{names: list<string>} */
    public function __debugInfo(): array
    {
        return ['names' => $this->reader()->names()];
    }

    private function reader(): Reader
    {
        return Reader::server();
    }
}
