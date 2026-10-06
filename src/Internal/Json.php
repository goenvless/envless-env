<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Json
{
    public static function object(string $text): ?\stdClass
    {
        try {
            $value = json_decode($text, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        return $value instanceof \stdClass ? $value : null;
    }

    public static function file(string $path): ?\stdClass
    {
        $text = is_file($path) ? @file_get_contents($path) : false;

        return \is_string($text) ? self::object($text) : null;
    }

    /** @param array<string, mixed> $payload */
    public static function pretty(array $payload): string
    {
        $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR;

        return (string) preg_replace_callback('/^(?: {4})+/m', static fn(array $match): string => str_repeat(' ', intdiv(\strlen($match[0]), 2)), json_encode($payload, $flags));
    }
}
