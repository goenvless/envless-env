<?php

declare(strict_types=1);

namespace Envless\Env\Types;

use Envless\Exception\InvalidArgumentException;

final class Options
{
    private const IDENTIFIER = '[A-Za-z_][A-Za-z0-9_]*';

    private const FLAGS = ['--output' => 'output', '--class' => 'class', '--namespace' => 'namespace', '--project' => 'project', '--product' => 'product', '--env' => 'environments'];

    /** @param list<string> $environments */
    private function __construct(
        public readonly string $output,
        public readonly string $class,
        public readonly string $namespace,
        public readonly ?string $project,
        public readonly ?string $product,
        public readonly array $environments,
        public readonly bool $check,
    ) {}

    /** @param list<string> $arguments */
    public static function parse(array $arguments): self
    {
        $values = [];
        $check = false;

        for ($index = 0; $index < \count($arguments); $index++) {
            [$flag, $inline] = str_contains($arguments[$index], '=') ? explode('=', $arguments[$index], 2) : [$arguments[$index], null];

            if ($flag === '--check') {
                $check = true;
            } elseif (isset(self::FLAGS[$flag])) {
                $values[self::FLAGS[$flag]] = $inline ?? $arguments[++$index] ?? throw new InvalidArgumentException($flag . ' needs a value.');
            } else {
                throw new InvalidArgumentException('Unknown option ' . $flag . '. Use --output, --class, --namespace, --project, --product, --env or --check.');
            }
        }

        $class = $values['class'] ?? 'EnvlessEnv';
        $namespace = trim($values['namespace'] ?? '', '\\');

        if (preg_match('/\A' . self::IDENTIFIER . '\z/', $class) !== 1) {
            throw new InvalidArgumentException('--class must be a PHP class name, not ' . $class . '.');
        }

        if ($namespace !== '' && preg_match('/\A' . self::IDENTIFIER . '(?:\\\\' . self::IDENTIFIER . ')*\z/', $namespace) !== 1) {
            throw new InvalidArgumentException('--namespace must be a PHP namespace, not ' . $namespace . '.');
        }

        $environments = array_values(array_filter(array_map(trim(...), explode(',', $values['environments'] ?? '')), static fn(string $slug): bool => $slug !== ''));

        return new self($values['output'] ?? 'EnvlessEnv.php', $class, $namespace, $values['project'] ?? null, $values['product'] ?? null, $environments, $check);
    }
}
