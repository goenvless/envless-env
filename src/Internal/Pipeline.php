<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

use Envless\Env\Exception\LoadException;
use Envless\Http\HttpClient;

final class Pipeline
{
    /** @return array<string, string> */
    public static function run(bool $revalidate, ?HttpClient $http): array
    {
        $api = new Api(Token::resolve(), null, $http);
        $target = Target::resolve();
        $keys = Machine::keys($api);
        $cached = $revalidate ? null : Cache::read($target);

        if ($cached !== null && $cached->isFresh()) {
            $opened = Decrypt::entries($cached->variables, $keys);

            if ($opened->failed === null) {
                return Finalize::run($opened->entries, $cached->variables);
            }
        }

        try {
            $variables = $api->variables($target);
        } catch (ApiFailure $failure) {
            return self::stale($target, $keys, $failure, $revalidate ? null : $cached)
                ?? throw new LoadException(\sprintf(Messages::LOAD_FAILED, $target->project, $target->environment, $failure->getMessage()), 0, $failure);
        }

        Cache::write($target, $variables);
        $opened = Decrypt::entries($variables, $keys);

        if ($opened->failed !== null) {
            throw new LoadException(\sprintf(Messages::DECRYPT_FAILED, $opened->failed, Protocol::ENV_VARS['KEY'], Protocol::ENV_VARS['PASSPHRASE']));
        }

        return Finalize::run($opened->entries, $variables);
    }

    /** @return array<string, string>|null */
    private static function stale(Target $target, Keys $keys, ApiFailure $failure, ?Cached $cached): ?array
    {
        if (!$failure->transient || $cached === null) {
            return null;
        }

        $opened = Decrypt::entries($cached->variables, $keys);

        if ($opened->failed !== null) {
            return null;
        }

        Warn::emit(\sprintf(Messages::STALE, $target->project, $target->environment, $failure->getMessage(), $cached->cachedAt));

        return Finalize::run($opened->entries, $cached->variables);
    }
}
