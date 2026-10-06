<?php

declare(strict_types=1);

namespace Envless\Env\Types;

use Envless\Env\Exception\LoadException;
use Envless\Env\Internal\Api;
use Envless\Env\Internal\ApiFailure;
use Envless\Env\Internal\Binding;
use Envless\Env\Internal\Environment;
use Envless\Env\Internal\Json;
use Envless\Env\Internal\Messages;
use Envless\Env\Internal\Protocol;
use Envless\Env\Internal\Target;
use Envless\Env\Internal\Token;
use Envless\Env\Internal\Unsafe;
use Envless\Http\HttpClient;

final class Metadata
{
    /** @return array<string, array{type: string, visibility: string}> */
    public static function collect(Options $options, ?HttpClient $http = null): array
    {
        $manifest = Environment::filled(Protocol::INJECTED_MANIFEST['NAME']);

        if ($manifest !== null && $options->project === null && $options->environments === []) {
            return self::injected(Json::object($manifest) ?? new \stdClass());
        }

        $binding = Binding::read();
        $names = Protocol::ENV_VARS;
        $project = $options->project ?? Environment::get($names['PROJECT']) ?? $binding?->project;
        $product = $options->product ?? Environment::get($names['PRODUCT']) ?? $binding?->product;
        $from = Environment::get($names['ENV']);
        $environments = $options->environments !== [] ? $options->environments : ($from !== null ? [$from] : ($binding === null ? [] : $binding->environments));

        if ($project === null || $project === '') {
            throw new LoadException(\sprintf(Messages::MISSING_PROJECT, $names['PROJECT']));
        }

        if (array_filter($environments, static fn(string $slug): bool => $slug !== '') === []) {
            throw new LoadException(\sprintf(Messages::MISSING_ENVIRONMENT, $names['ENV']));
        }

        $api = new Api(Token::resolve(), null, $http);
        $metadata = [];

        foreach ($environments as $environment) {
            try {
                $variables = $api->variables(new Target($project, $environment, $product, $binding?->workspace));
            } catch (ApiFailure $failure) {
                throw new LoadException(\sprintf(Messages::LOAD_FAILED, $project, $environment, $failure->getMessage()), 0, $failure);
            }

            foreach ($variables as $variable) {
                $known = array_map(strtolower(...), array_keys($metadata));
                $metadata += \in_array(strtolower($variable->name), $known, true) ? [] : [$variable->name => $variable->meta()];
            }
        }

        return $metadata;
    }

    /** @return array<string, array{type: string, visibility: string}> */
    private static function injected(\stdClass $manifest): array
    {
        $metadata = [];
        $keys = Protocol::INJECTED_MANIFEST;

        foreach (get_object_vars($manifest) as $key => $entry) {
            $name = (string) $key;
            $type = $entry instanceof \stdClass ? ($entry->{$keys['TYPE_KEY']} ?? null) : null;
            $visibility = $entry instanceof \stdClass ? ($entry->{$keys['VISIBILITY_KEY']} ?? null) : null;

            if (!Unsafe::is($name)) {
                $metadata[$name] = [
                    'type' => \is_string($type) && \in_array($type, Protocol::VAR_TYPES, true) ? $type : Protocol::DEFAULT_VAR_META['type'],
                    'visibility' => \is_string($visibility) && \in_array($visibility, Protocol::VAR_VISIBILITY, true) ? $visibility : Protocol::DEFAULT_VAR_META['visibility'],
                ];
            }
        }

        return $metadata;
    }
}
