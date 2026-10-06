<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

use Envless\Http\HttpClient;

final class Loader
{
    /** @var array<string, string>|null */
    private static ?array $memo = null;

    /** @return array<string, string> */
    public static function load(bool $revalidate, ?HttpClient $http): array
    {
        UpdateNotice::check();

        if (Store::isInjected() && !$revalidate) {
            return Records::snapshot();
        }

        if (self::$memo !== null && !$revalidate) {
            return self::$memo;
        }

        self::$memo = null;

        return self::$memo = Pipeline::run($revalidate, $http);
    }
}
