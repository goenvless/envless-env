<?php

declare(strict_types=1);

namespace Envless\Env;

use Envless\Env\Internal\Layers;
use Envless\Env\Internal\Messages;
use Envless\Exception\InvalidArgumentException;

final class Override
{
    private bool $restored = false;

    private function __construct(private readonly int $layer) {}

    /** @param array<string, string> $values */
    public static function push(array $values): self
    {
        foreach ($values as $name => $value) {
            if (!\is_string($value)) {
                throw new InvalidArgumentException(\sprintf(Messages::OVERRIDE_VALUE, $name, get_debug_type($value)));
            }
        }

        return new self(Layers::push($values));
    }

    public function restore(): void
    {
        if (!$this->restored) {
            $this->restored = true;
            Layers::pop($this->layer);
        }
    }

    public function isRestored(): bool
    {
        return $this->restored;
    }
}
