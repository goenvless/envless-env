# Changelog

## 0.0.1

The first release of the PHP runtime. It loads, caches, coerces and refuses exactly as `@goenvless/env` does as it stands in this repository. `composer parity` replays every scenario against both packages and fails on any difference in the requests, the retries and their pauses, the values, the types, the errors, the process environment, the warnings or the cache files, byte for byte with their modes.

### Added

`Envless\Env\Env::load()` fetches your environment with an `ev_sk_` machine key, decrypts it with `ENVLESS_KEY` or `ENVLESS_PASSPHRASE`, caches the ciphertext for 15 minutes in the same files `@goenvless/env` writes, and fills `$_ENV`, `$_SERVER` and `getenv()` without overwriting anything already set or writing a name that changes how a process executes code. `vendor/envless/env/register.php` does the same from PHP's `auto_prepend_file`, and inside `envless run` the injected values are used with no credentials and no request.

    Envless\Env\Env::load();
    $port = Envless\Env\Env::number('PORT');

`Env::get()`, `optional()` and `has()` read each variable coerced to its declared type, `string()`, `number()` and `boolean()` read it typed for PHPStan and Psalm, and `Env::server()` and `Env::client()` are views that read with `->`, `[]` and `foreach`. The client view holds only the variables marked client and is the one you may `json_encode()`. The server view refuses `json_encode()` and `serialize()`, and dumps show names, never values. `Env::override()` layers values for tests, `Env::fetch()` loads with explicit arguments for serverless and multi-tenant code, and `Env::isConfigured()` lets a build without credentials skip the load.

`vendor/bin/envless-env types` writes a class with one typed static method per variable, from the manifest under `envless run` or with a machine key, and `--check` fails CI when the class is stale. `Envless\Env\Symfony\EnvVarLoader` resolves Symfony's `%env()%` from Envless.

Every failure throws a subclass of `Envless\Env\Exception\EnvException`, which implements `Envless\Exception\EnvlessException` like every `envless/sdk` exception: `LoadException`, `NotLoadedException`, `MissingVariableException`, `ServerOnlyException`, `ReadOnlyException` and `SerialisationException`.

For PHP-FPM, where every request starts from scratch, three things are PHP's own: `.envless` is also looked up at the Composer project root, because FPM runs requests from `public/`; the cache moves to `envless-<uid>` under the system temp folder when the home folder is not writable; and when the cache has expired and the API cannot be reached, times out or answers `408`, `429` or `5xx`, the last cached values are served with a warning, the way a Node process keeps its values through an outage. A refused key, a missing environment and `Env::load(revalidate: true)` never fall back.

The package needs PHP 8.2 or newer and installs `envless/sdk`, whose cURL transport, retries and encryption it uses.
