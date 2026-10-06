<?php

declare(strict_types=1);

namespace Envless\Env\Types;

use Envless\Exception\EnvlessException;
use Envless\Http\HttpClient;

final class Command
{
    public const USAGE = 'usage: vendor/bin/envless-env types [--output EnvlessEnv.php] [--class EnvlessEnv] [--namespace App\\\\Support] [--project slug] [--product slug] [--env a,b] [--check]';

    /**
     * @param list<string> $arguments
     * @param resource $output
     * @param resource $errors
     */
    public static function run(array $arguments, $output, $errors, ?HttpClient $http = null): int
    {
        if (($arguments[0] ?? null) !== 'types') {
            fwrite($errors, self::USAGE . PHP_EOL);

            return 2;
        }

        try {
            $options = Options::parse(\array_slice($arguments, 1));
            $metadata = Metadata::collect($options, $http);
        } catch (EnvlessException $error) {
            fwrite($errors, $error->getMessage() . PHP_EOL);

            return 1;
        }

        $rendered = Render::file($options, $metadata);
        $current = is_file($options->output) ? file_get_contents($options->output) : null;

        foreach (Render::skipped($metadata) as $name) {
            fwrite($errors, $name . ' has no typed method, because it clashes with a reserved name. Read it with Env::get(\'' . $name . '\').' . PHP_EOL);
        }

        if ($options->check) {
            if ($current === $rendered) {
                fwrite($output, $options->output . ' matches the ' . \count($metadata) . ' variables in Envless.' . PHP_EOL);

                return 0;
            }

            fwrite($errors, $options->output . ' ' . ($current === null ? 'is missing' : 'is stale') . '. Run vendor/bin/envless-env types and commit the result.' . PHP_EOL);

            return 1;
        }

        if ($current !== $rendered && @file_put_contents($options->output, $rendered) === false) {
            fwrite($errors, 'Could not write ' . $options->output . '.' . PHP_EOL);

            return 1;
        }

        fwrite($output, ($current === $rendered ? $options->output . ' is already current with ' : 'Wrote ' . $options->output . ' with ') . \count($metadata) . ' variables.' . PHP_EOL);

        return 0;
    }
}
