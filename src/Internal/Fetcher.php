<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

use Envless\Env\Exception\LoadException;
use Envless\Http\HttpClient;

final class Fetcher
{
    /** @return array<string, string> */
    public static function fetch(
        #[\SensitiveParameter]
        ?string $token,
        ?string $project,
        ?string $environment,
        ?string $product,
        #[\SensitiveParameter]
        ?string $passphrase,
        #[\SensitiveParameter]
        ?string $key,
        ?string $workspaceId,
        ?string $endpoint,
        ?HttpClient $http,
    ): array {
        if ($token === null || $token === '') {
            throw new LoadException(Messages::FETCH_NO_TOKEN);
        }

        if ($project === null || $project === '' || $environment === null || $environment === '') {
            throw new LoadException(Messages::FETCH_NO_TARGET);
        }

        if (($passphrase === null || $passphrase === '') && ($key === null || $key === '')) {
            throw new LoadException(Messages::FETCH_NO_KEY_MATERIAL);
        }

        $api = new Api($token, $endpoint === '' ? null : $endpoint, $http);
        $keys = self::keys($api, $passphrase, $key, $workspaceId);

        try {
            $variables = $api->variables(new Target($project, $environment, $product, null));
        } catch (ApiFailure $failure) {
            throw new LoadException($failure->getMessage(), 0, $failure);
        }

        $opened = Decrypt::entries($variables, $keys);

        if ($opened->failed !== null) {
            throw new LoadException(\sprintf(Messages::FETCH_DECRYPT_FAILED, $opened->failed));
        }

        return $opened->entries;
    }

    private static function keys(Api $api, #[\SensitiveParameter] ?string $passphrase, #[\SensitiveParameter] ?string $key, ?string $workspaceId): Keys
    {
        try {
            $workspace = ($key === null || $key === '') && ($workspaceId === null || $workspaceId === '') ? $api->workspaceId() : $workspaceId;
        } catch (ApiFailure $failure) {
            throw new LoadException($failure->getMessage(), 0, $failure);
        }

        try {
            return KeyMaterial::resolve($key, $passphrase, $workspace);
        } catch (KeyFailure $failure) {
            throw new LoadException(match ($failure->getCode()) {
                KeyMaterial::LOCKED => Messages::FETCH_NO_KEY_MATERIAL,
                KeyMaterial::NO_WORKSPACE => Messages::FETCH_NO_WORKSPACE,
                default => $failure->getMessage(),
            }, 0, $failure);
        }
    }
}
