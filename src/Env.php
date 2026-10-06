<?php

declare(strict_types=1);

namespace Envless\Env;

use Envless\Env\Internal\Coerce;
use Envless\Env\Internal\Fetcher;
use Envless\Env\Internal\Loader;
use Envless\Env\Internal\Reader;
use Envless\Env\Internal\Store;
use Envless\Env\Internal\Token;
use Envless\Http\HttpClient;

final class Env
{
    public const VERSION = '0.0.1';

    private function __construct() {}

    /** @return array<string, string> */
    public static function load(bool $revalidate = false, ?HttpClient $httpClient = null): array
    {
        return Loader::load($revalidate, $httpClient);
    }

    public static function isConfigured(): bool
    {
        return Store::isInjected() || Token::available();
    }

    public static function get(string $name): string|int|float|bool
    {
        return Reader::server()->value($name);
    }

    public static function optional(string $name): string|int|float|bool|null
    {
        return Reader::server()->optional($name);
    }

    public static function has(string $name): bool
    {
        return Reader::server()->has($name);
    }

    public static function string(string $name): string
    {
        return Reader::server()->raw($name);
    }

    public static function number(string $name): int|float
    {
        return Coerce::number(Reader::server()->raw($name));
    }

    public static function boolean(string $name): bool
    {
        return Coerce::boolean(Reader::server()->raw($name));
    }

    public static function server(): Values
    {
        return new Values();
    }

    public static function client(): ClientValues
    {
        return new ClientValues();
    }

    /** @param array<string, string> $values */
    public static function override(array $values): Override
    {
        return Override::push($values);
    }

    /** @return array<string, string> */
    public static function fetch(
        #[\SensitiveParameter]
        ?string $token = null,
        ?string $project = null,
        ?string $environment = null,
        ?string $product = null,
        #[\SensitiveParameter]
        ?string $passphrase = null,
        #[\SensitiveParameter]
        ?string $key = null,
        ?string $workspaceId = null,
        ?string $endpoint = null,
        ?HttpClient $httpClient = null,
    ): array {
        return Fetcher::fetch($token, $project, $environment, $product, $passphrase, $key, $workspaceId, $endpoint, $httpClient);
    }
}
