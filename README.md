<div align='center'>
   <a href='https://envless.cloud'>
        <img
            src='https://cdn-prod.envless.cloud/assets/npm/logo.svg'
            alt='Envless Logo'
            width='180'
        />
   </a>

   <br />
</div>

<p align='center'>
    End-to-end encrypted environment variables for teams. Load them into your PHP app at boot, decrypted on the machine that runs it.
</p>

<p align='center'>
    <a href='https://envless.cloud'>
        <b>
            Website
        </b>
    </a>
    •
    <a href='https://envless.cloud/dashboard'>
        <b>
            Dashboard
        </b>
    </a>
    •
    <a href='https://envless.cloud/docs/env-php/overview'>
        <b>
            Documentation
        </b>
    </a>
    •
    <a href='https://status.envless.cloud'>
        <b>
            Services Status
        </b>
    </a>
</p>

<br />

## Envless runtime for PHP

`envless/env` fetches your environment from Envless when your app starts, decrypts it on the machine that runs it, and hands it to your code typed. It is the PHP twin of `@goenvless/env`, with the same loader, the same on-disk cache and the same rules, and it runs on PHP 8.2 and newer. Nothing decrypted is written to disk.

### Installing
```bash
composer require envless/env
```

### Loading
```php
use Envless\Env\Env;

Env::load();

$databaseUrl = Env::string('DATABASE_URL');
$port = Env::get('PORT');
```

`Env::load()` reads `ENVLESS_TOKEN`, an `ev_sk_` machine key, and `ENVLESS_KEY`, the raw workspace key, or `ENVLESS_PASSPHRASE`. The project and environment come from `ENVLESS_PROJECT` and `ENVLESS_ENV`, or from the `.envless` file `envless link` writes, which is found in the working directory or at your Composer project root. Values are cached as ciphertext under `~/.envless/cache` for 15 minutes, shared with the Node runtime, and written into `$_ENV`, `$_SERVER` and `getenv()` without overwriting anything already set.

To load without touching your code, point PHP's `auto_prepend_file` at `vendor/envless/env/register.php`. Inside `envless run`, the values the CLI injected are used and no request is made.

### Laravel
```php
use Envless\Env\Env;

if (Env::isConfigured()) {
    Env::load();
}
```

Put it at the top of `bootstrap/app.php`. Every `env()` call, every `config()` value and every queue worker then reads Envless, and a `composer install` without credentials still boots from `.env`. Symfony resolves `%env()%` from Envless through `Envless\Env\Symfony\EnvVarLoader`, tagged `container.env_var_loader`.

### Reading
```php
use Envless\Env\Env;

$host = Env::string('DB_HOST');
$port = Env::number('DB_PORT');
$debug = Env::boolean('APP_DEBUG');
$dsn = Env::optional('SENTRY_DSN');
$public = Env::client()->snapshot();
```

`Env::get()` coerces each value to the type set on the variable in Envless: a `number` reads back as an `int` or a `float`, a `boolean` as a `bool`. A missing name throws `MissingVariableException`, and `Env::optional()` returns `null` instead. `Env::client()` reads only the variables marked client, so it is the one to hand to a template, and `json_encode(Env::server())` and `serialize()` refuse to copy the secrets out.

### Typed classes
```bash
vendor/bin/envless-env types --output app/Support/EnvlessEnv.php --namespace 'App\Support'
```

The command writes a class with one typed static method per variable, from the variable names, types and visibility, never their values, so PHPStan, Psalm and your IDE catch a misspelt name. `--check` fails CI when the committed class is stale.

### Tests
```php
use Envless\Env\Env;

$override = Env::override(['FEATURE_CHECKOUT' => 'true']);
$enabled = Env::boolean('FEATURE_CHECKOUT');
$override->restore();
```

### Serverless and explicit loading
```php
use Envless\Env\Env;

$values = Env::fetch(token: (string) getenv('ENVLESS_TOKEN'), project: 'api', environment: 'production', key: (string) getenv('ENVLESS_KEY'));
```

`Env::fetch()` takes every input as an argument and keeps nothing: no cache, no memo and no write to the process environment.

### PHP-FPM
Under PHP-FPM every request loads from the cache, which costs well under a millisecond with `ENVLESS_KEY`. Use `ENVLESS_KEY` in production: `ENVLESS_PASSPHRASE` derives the key on every request. When the home folder is not writable, as for `www-data`, the cache moves to the system temp folder, and when the cache has expired and the API cannot be reached, the last cached values keep the site up and a warning goes to the error log. A refused key never falls back.

Every failure throws a subclass of `Envless\Env\Exception\EnvException`: `LoadException`, `NotLoadedException`, `MissingVariableException`, `ServerOnlyException`, `ReadOnlyException` or `SerialisationException`.

For the full guide, see the [documentation](https://envless.cloud/docs/env-php/overview).
