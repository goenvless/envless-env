<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

use Envless\Envless;

final class KeyMaterial
{
    public const LOCKED = 1;

    public const NO_WORKSPACE = 2;

    public const INVALID = 3;

    public static function resolve(#[\SensitiveParameter] ?string $key, #[\SensitiveParameter] ?string $passphrase, ?string $workspaceId): Keys
    {
        if ($key !== null && $key !== '') {
            try {
                return new Keys(Envless::importWorkspaceKey($key), null);
            } catch (\Throwable $error) {
                throw new KeyFailure(\sprintf(Messages::KEY_INVALID, Protocol::ENV_VARS['KEY'], $error->getMessage()), self::INVALID, $error);
            }
        }

        if ($passphrase === null || $passphrase === '') {
            throw new KeyFailure('locked', self::LOCKED);
        }

        if ($workspaceId === null || $workspaceId === '') {
            throw new KeyFailure('no-workspace', self::NO_WORKSPACE);
        }

        try {
            $derived = Envless::deriveWorkspaceKey($passphrase, $workspaceId);
        } catch (\Throwable $error) {
            throw new KeyFailure($error->getMessage(), self::INVALID, $error);
        }

        return new Keys($derived, static fn(string $encoded): string => Envless::decryptValue($encoded, $passphrase, $workspaceId));
    }
}
