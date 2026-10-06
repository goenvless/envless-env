<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Query
{
    public static function segment(string $value): string
    {
        return strtr(rawurlencode($value), ['%21' => '!', '%27' => "'", '%28' => '(', '%29' => ')', '%2A' => '*']);
    }

    /** @param array<string, mixed> $query */
    public static function url(string $baseUrl, string $path, array $query): string
    {
        $pairs = [];

        foreach ($query as $key => $value) {
            if ($value !== null && $value !== '') {
                $pairs[] = self::component($key) . '=' . self::component(\is_scalar($value) ? (string) $value : '');
            }
        }

        return rtrim($baseUrl, '/') . $path . ($pairs === [] ? '' : '?' . implode('&', $pairs));
    }

    public static function truthy(mixed $value): bool
    {
        return match (true) {
            $value === null, $value === false, $value === '' => false,
            \is_int($value) => $value !== 0,
            \is_float($value) => !is_nan($value) && $value !== 0.0,
            default => true,
        };
    }

    private static function component(string $text): string
    {
        return strtr(rawurlencode($text), ['%20' => '+', '%2A' => '*', '~' => '%7E']);
    }
}
