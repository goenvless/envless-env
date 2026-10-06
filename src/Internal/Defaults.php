<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

use Envless\Env\Env;

final class Defaults
{
    public const PACKAGE = 'envless/env';

    public const USER_AGENT = 'envless-env-php/' . Env::VERSION;

    public const UPDATE_NOTICE = [
        'REGISTRY_URL' => 'https://repo.packagist.org/p2/envless/env.json',
        'COMMAND' => 'composer update envless/env',
    ];

    public const TEMP_DIRECTORY = 'envless-';

    public const SAFE_INTEGER = 9007199254740991;

    public const JS_SPACE = '[\x{0009}-\x{000D}\x{0020}\x{00A0}\x{1680}\x{2000}-\x{200A}\x{2028}\x{2029}\x{202F}\x{205F}\x{3000}\x{FEFF}]';

    public const SLUG_UNSAFE = '/[^a-zA-Z0-9._-]/';

    public const ISO_TIME = '/\A([+-]\d{6}|\d{4})(?:-(\d{2})(?:-(\d{2}))?)?(?:T(\d{2}):(\d{2})(?::(\d{2})(?:\.(\d+))?)?(Z|[+-]\d{2}:\d{2})?)?\z/';

    private function __construct() {}
}
