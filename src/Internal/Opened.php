<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Opened
{
    /** @param array<string, string> $entries */
    public function __construct(public readonly array $entries, public readonly ?string $failed) {}
}
