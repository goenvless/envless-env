<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

use Envless\Env\Exception\LoadException;

final class Machine
{
    public static function keys(Api $api): Keys
    {
        $names = Protocol::ENV_VARS;
        $key = Environment::filled($names['KEY']);
        $passphrase = Environment::filled($names['PASSPHRASE']);

        if ($key === null && $passphrase === null) {
            throw new LoadException(\sprintf(Messages::NO_KEY_MATERIAL, $names['KEY'], $names['PASSPHRASE']));
        }

        try {
            $workspaceId = $key === null ? $api->workspaceId() : null;
        } catch (ApiFailure $failure) {
            throw new LoadException($failure->getMessage(), 0, $failure);
        }

        try {
            return KeyMaterial::resolve($key, $passphrase, $workspaceId);
        } catch (KeyFailure $failure) {
            $message = $failure->getCode() === KeyMaterial::NO_WORKSPACE ? \sprintf(Messages::NO_WORKSPACE, $names['PASSPHRASE']) : $failure->getMessage();

            throw new LoadException($message, 0, $failure);
        }
    }
}
