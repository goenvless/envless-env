<?php

declare(strict_types=1);

namespace Envless\Env;

use Envless\Env\Internal\Reader;

/**
 * @implements \ArrayAccess<string, string|int|float|bool>
 * @implements \IteratorAggregate<string, string|int|float|bool>
 */
final class ClientValues implements \ArrayAccess, \IteratorAggregate, \Countable, \JsonSerializable
{
    use GuardsValues;
    use ReadsValues;

    /** @return array<string, string|int|float|bool> */
    public function snapshot(): array
    {
        return $this->reader()->snapshot();
    }

    /** @return array<string, string|int|float|bool> */
    public function jsonSerialize(): array
    {
        return $this->reader()->snapshot();
    }

    /** @return array<string, string|int|float|bool> */
    public function __debugInfo(): array
    {
        return $this->reader()->snapshot();
    }

    private function reader(): Reader
    {
        return Reader::client();
    }
}
