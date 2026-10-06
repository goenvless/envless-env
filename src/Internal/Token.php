<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

use Envless\Env\Exception\LoadException;

final class Token
{
    public static function resolve(): string
    {
        $name = Protocol::ENV_VARS['TOKEN'];
        $token = Environment::filled($name) ?? self::configured();

        if ($token === null) {
            throw new LoadException(\sprintf(Messages::MISSING_TOKEN, $name, Protocol::MACHINE_TOKEN_PREFIX));
        }

        if (!str_starts_with($token, Protocol::MACHINE_TOKEN_PREFIX)) {
            throw new LoadException(\sprintf(Messages::NOT_MACHINE, $name, Protocol::MACHINE_TOKEN_PREFIX));
        }

        return $token;
    }

    public static function available(): bool
    {
        return Environment::filled(Protocol::ENV_VARS['TOKEN']) !== null || str_starts_with(self::configured() ?? '', Protocol::MACHINE_TOKEN_PREFIX);
    }

    private static function configured(): ?string
    {
        $home = Home::directory();
        $config = $home === null ? null : Json::file($home . '/' . Protocol::CONFIG['DIR'] . '/' . Protocol::CONFIG['FILE']);
        $token = $config->token ?? null;

        return \is_string($token) && $token !== '' ? $token : null;
    }
}
