<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class CacheRoot
{
    public function __construct(public readonly string $base, public readonly string $cache) {}
}
