<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

use Envless\Env\Exception\LoadException;

final class Target
{
    public function __construct(public readonly string $project, public readonly string $environment, public readonly ?string $product, public readonly ?string $workspace) {}

    public static function resolve(): self
    {
        $binding = Binding::read();
        $names = Protocol::ENV_VARS;
        $product = Environment::get($names['PRODUCT']) ?? $binding?->product;
        $project = Environment::get($names['PROJECT']) ?? $binding?->project;
        $environment = Environment::get($names['ENV']) ?? $binding?->environment;

        if ($project === null || $project === '') {
            throw new LoadException(\sprintf(Messages::MISSING_PROJECT, $names['PROJECT']));
        }

        if ($environment === null || $environment === '') {
            throw new LoadException(\sprintf(Messages::MISSING_ENVIRONMENT, $names['ENV']));
        }

        return new self($project, $environment, $product, $binding?->workspace);
    }
}
