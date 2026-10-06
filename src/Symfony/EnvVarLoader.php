<?php

declare(strict_types=1);

namespace Envless\Env\Symfony;

use Envless\Env\Env;
use Symfony\Component\DependencyInjection\EnvVarLoaderInterface;

final class EnvVarLoader implements EnvVarLoaderInterface
{
    /** @return array<string, string> */
    public function loadEnvVars(): array
    {
        return Env::isConfigured() ? Env::load() : [];
    }
}
