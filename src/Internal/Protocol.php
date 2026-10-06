<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Protocol
{
    public const CACHE = ['MAX_AGE_MS' => 900000, 'NO_PRODUCT' => '_'];

    public const CONFIG = ['DIR' => '.envless', 'FILE' => 'config.json', 'CACHE_DIR' => 'cache'];

    public const CRYPTO = [
        'PBKDF2_ITERATIONS' => 200000,
        'PBKDF2_ITERATIONS_MIN' => 100000,
        'PBKDF2_ITERATIONS_MAX' => 1000000,
        'HASH' => 'SHA-256',
        'AES_ALGORITHM' => 'AES-GCM',
        'KDF_ALGORITHM' => 'PBKDF2',
        'AES_LENGTH' => 256,
        'IV_LENGTH' => 12,
        'SALT_LENGTH' => 16,
        'WORKSPACE_SALT_NAMESPACE' => 'envless:workspace:',
        'CIPHERTEXT_VERSION_PREFIX' => 'v1:',
        'CIPHERTEXT_VERSION_PREFIX_V2' => 'v2:',
    ];

    public const DEFAULT_VAR_META = ['type' => 'string', 'visibility' => 'server'];

    public const ENVLESS_FILE = '.envless';

    public const ENV_READ_ONLY = 'Envless env is read-only.';

    public const ENV_VARS = [
        'VERBOSE' => 'ENVLESS_VERBOSE',
        'TOKEN' => 'ENVLESS_TOKEN',
        'KEY' => 'ENVLESS_KEY',
        'PASSPHRASE' => 'ENVLESS_PASSPHRASE',
        'PRODUCT' => 'ENVLESS_PRODUCT',
        'PROJECT' => 'ENVLESS_PROJECT',
        'ENV' => 'ENVLESS_ENV',
    ];

    public const FILE_MODES = ['DIR' => 448, 'FILE' => 384];

    public const INJECTED_MANIFEST = ['NAME' => 'ENVLESS_INJECTED', 'TYPE_KEY' => 't', 'VISIBILITY_KEY' => 'v'];

    public const MACHINE_TOKEN_PREFIX = 'ev_sk_';

    public const PUBLIC_PAGE_LIMIT = 100;

    public const RETRYABLE_STATUSES = [408, 429, 500, 502, 503, 504];

    public const UNSAFE_ENV_WARNING = '[envless] These variables were decrypted but not written into the process environment, because their names change how this process or its children execute code, so storing one turns write access to a variable into code execution: ';

    public const UNSAFE_PROCESS_ENV_NAMES = [
        'NODE_OPTIONS' => 'NODE_OPTIONS',
        'NODE_PATH' => 'NODE_PATH',
        'NODE_REPL_EXTERNAL_MODULE' => 'NODE_REPL_EXTERNAL_MODULE',
        'ELECTRON_RUN_AS_NODE' => 'ELECTRON_RUN_AS_NODE',
        'LD_PRELOAD' => 'LD_PRELOAD',
        'LD_LIBRARY_PATH' => 'LD_LIBRARY_PATH',
        'LD_AUDIT' => 'LD_AUDIT',
        'DYLD_INSERT_LIBRARIES' => 'DYLD_INSERT_LIBRARIES',
        'DYLD_LIBRARY_PATH' => 'DYLD_LIBRARY_PATH',
        'DYLD_FRAMEWORK_PATH' => 'DYLD_FRAMEWORK_PATH',
        'PATH' => 'PATH',
        'BUN_INSPECT' => 'BUN_INSPECT',
        'BUN_INSPECT_CONNECT_TO' => 'BUN_INSPECT_CONNECT_TO',
        'RUBYOPT' => 'RUBYOPT',
        'RUBYLIB' => 'RUBYLIB',
        'BUNDLE_GEMFILE' => 'BUNDLE_GEMFILE',
        'GEM_HOME' => 'GEM_HOME',
        'GEM_PATH' => 'GEM_PATH',
        'PYTHONPATH' => 'PYTHONPATH',
        'PYTHONHOME' => 'PYTHONHOME',
        'PYTHONSTARTUP' => 'PYTHONSTARTUP',
        'PYTHONUSERBASE' => 'PYTHONUSERBASE',
        'PHPRC' => 'PHPRC',
        'PHP_INI_SCAN_DIR' => 'PHP_INI_SCAN_DIR',
        'CLIENT_ENV_CHANNEL' => 'ENVLESS_CLIENT_ENV',
        'INJECTED_MANIFEST' => 'ENVLESS_INJECTED',
    ];

    public const UNSUPPORTED_VARIABLE_FORMAT = 'Unsupported variable format';

    public const UPDATE_NOTICE = [
        'ENV_DISABLE' => 'ENVLESS_DISABLE_UPDATE_NOTICE',
        'REGISTRY_URL' => 'https://registry.npmjs.org/@goenvless/env',
        'TIMEOUT_MS' => 2000,
        'PACKAGE' => '@goenvless/env',
        'CHANGELOG_URL' => 'https://envless.cloud/docs/reference/changelogs-runtime',
        'ICON' => 'ℹ',
    ];

    public const VAR_TYPES = ['STRING' => 'string', 'NUMBER' => 'number', 'BOOLEAN' => 'boolean'];

    public const VAR_VISIBILITY = ['SERVER' => 'server', 'CLIENT' => 'client'];

    private function __construct() {}
}
