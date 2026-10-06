<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Cached
{
    /** @param list<Variable> $variables */
    public function __construct(public readonly array $variables, public readonly string $cachedAt) {}

    public function isFresh(): bool
    {
        return CacheFile::isFresh($this->cachedAt);
    }
}
