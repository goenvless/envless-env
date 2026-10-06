<?php

declare(strict_types=1);

namespace Envless\Env\Types;

use Envless\Env\Internal\Protocol;

final class Render
{
    public const RESERVED = ['class', 'client'];

    private const RETURNS = ['string' => ['string', 'string'], 'number' => ['int|float', 'number'], 'boolean' => ['bool', 'boolean']];

    /** @param array<string, array{type: string, visibility: string}> $metadata */
    public static function file(Options $options, array $metadata): string
    {
        $methods = [];
        $client = [];
        $shape = [];

        foreach ($metadata as $name => $meta) {
            if (self::isReserved($name)) {
                continue;
            }

            [$type, $reader] = self::RETURNS[$meta['type']] ?? self::RETURNS[Protocol::VAR_TYPES['STRING']];
            $methods[] = self::method($name, $type, 'Env::' . $reader . '(\'' . $name . '\')');

            if ($meta['visibility'] === Protocol::VAR_VISIBILITY['CLIENT']) {
                $client[] = '            \'' . $name . '\' => Env::client()->' . $reader . '(\'' . $name . '\'),';
                $shape[] = $name . ': ' . $type;
            }
        }

        $methods[] = implode("\n", [
            '    /** @return array{' . implode(', ', $shape) . '} */',
            '    public static function client(): array',
            '    {',
            ...($client === [] ? ['        return [];'] : ['        return [', ...$client, '        ];']),
            '    }',
        ]);

        return implode("\n", [
            '<?php',
            '',
            'declare(strict_types=1);',
            '',
            ...($options->namespace === '' ? [] : ['namespace ' . $options->namespace . ';', '']),
            'use Envless\Env\Env;',
            '',
            'final class ' . $options->class,
            '{',
            implode("\n\n", $methods),
            '}',
            '',
        ]);
    }

    /**
     * @param array<string, array{type: string, visibility: string}> $metadata
     * @return list<string>
     */
    public static function skipped(array $metadata): array
    {
        return array_values(array_filter(array_keys($metadata), self::isReserved(...)));
    }

    private static function isReserved(string $name): bool
    {
        return \in_array(strtolower($name), self::RESERVED, true) || str_starts_with($name, '__');
    }

    private static function method(string $name, string $type, string $read): string
    {
        return implode("\n", [
            '    public static function ' . $name . '(): ' . $type,
            '    {',
            '        return ' . $read . ';',
            '    }',
        ]);
    }
}
