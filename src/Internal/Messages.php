<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Messages
{
    public const NOT_LOADED = 'Envless env is not loaded yet. Call Envless\Env\Env::load() first, or prepend vendor/envless/env/register.php.';

    public const NOT_SERIALISABLE = 'Refusing to serialise the Envless env, because it holds decrypted secrets and this would copy every one of them into the output. Read the names you need, or call Env::load() for a plain array you own.';

    public const SERVER_ONLY = '%s is server only. Read it with Envless\Env\Env::get() instead.';

    public const MISSING = 'Missing env var: %s';

    public const READ_ONLY_OVERRIDE = ' Use Env::override() for temporary values.';

    public const OVERRIDE_VALUE = 'Env::override() takes string values, but %s is %s.';

    public const MISSING_TOKEN = 'Missing %s. Set it to an Envless machine key (%s…) to load variables.';

    public const NOT_MACHINE = '%s must be a machine key (%s…). Create one in the Envless dashboard.';

    public const MISSING_PROJECT = 'Missing %s. Set it to your project slug, or add a .envless file.';

    public const MISSING_ENVIRONMENT = 'Missing %s. Set it to your environment slug, or add a .envless file.';

    public const NO_KEY_MATERIAL = 'Set %s (a workspace key) or %s to decrypt in machine mode.';

    public const NO_WORKSPACE = '%s is set but the workspace could not be resolved to derive the key.';

    public const KEY_INVALID = '%s is invalid: %s';

    public const LOAD_FAILED = 'Could not load variables for %s/%s: %s';

    public const DECRYPT_FAILED = 'Could not decrypt %s. Check %s or %s.';

    public const TIMED_OUT = 'Request to %s timed out.';

    public const NOT_A_LIST = 'The variables in the response are not a list.';

    public const STALE = '[envless] Could not refresh %s/%s (%s), so the variables cached at %s were used instead.';

    public const FETCH_NO_TOKEN = 'Pass a machine key as token:. Env::fetch() reads no .envless file and no config to find one in.';

    public const FETCH_NO_TARGET = 'Pass project: and environment:. Env::fetch() reads no .envless file to resolve them from.';

    public const FETCH_NO_KEY_MATERIAL = 'Pass passphrase: or key:. Without one the values cannot be decrypted.';

    public const FETCH_NO_WORKSPACE = 'The workspace could not be resolved, so the passphrase cannot be turned into a key. Pass workspaceId: explicitly.';

    public const FETCH_DECRYPT_FAILED = 'Could not decrypt %s';

    public const UPDATE_AVAILABLE = '%s %s %s is available, you are on %s. Upgrade with %s. %s.';

    private function __construct() {}
}
