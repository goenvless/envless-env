<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

use Envless\Crypto\WorkspaceKey;
use Envless\Envless;

final class Keys
{
    /** @param (\Closure(string): string)|null $derive */
    public function __construct(private readonly WorkspaceKey $key, private readonly ?\Closure $derive) {}

    public function open(string $encoded): string
    {
        $iterations = Envelope::iterations($encoded);
        $pinned = $iterations !== null && $iterations !== Protocol::CRYPTO['PBKDF2_ITERATIONS'];

        return $pinned && $this->derive !== null ? ($this->derive)($encoded) : Envless::decryptWithKey($this->key, $encoded);
    }

    /** @return array{key: string} */
    public function __debugInfo(): array
    {
        return ['key' => '[redacted]'];
    }

    /** @return array<string, mixed> */
    public function __serialize(): array
    {
        throw new \LogicException('Workspace keys are never serialised.');
    }
}
