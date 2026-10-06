<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Numbers
{
    private const DECIMAL = '/\A[+-]?(?:[0-9]+\.?[0-9]*|\.[0-9]+)(?:[eE][+-]?[0-9]+)?\z/';

    private const INFINITY = '/\A([+-]?)Infinity\z/';

    private const RADIXES = ['0x' => ['/\A[0-9a-fA-F]+\z/', 16, 4], '0o' => ['/\A[0-7]+\z/', 8, 3], '0b' => ['/\A[01]+\z/', 2, 1]];

    private const MANTISSA_BITS = 53;

    public static function parse(string $text): float
    {
        $trimmed = self::trim($text);

        if ($trimmed === '') {
            return 0.0;
        }

        if (preg_match(self::DECIMAL, $trimmed) === 1) {
            return (float) $trimmed;
        }

        if (preg_match(self::INFINITY, $trimmed, $match) === 1) {
            return $match[1] === '-' ? -INF : INF;
        }

        return self::radix($trimmed);
    }

    public static function trim(string $text): string
    {
        return preg_replace('/\A' . Defaults::JS_SPACE . '+|' . Defaults::JS_SPACE . '+\z/u', '', $text) ?? trim($text, " \t\n\r\v\f");
    }

    private static function radix(string $text): float
    {
        $radix = self::RADIXES[strtolower(substr($text, 0, 2))] ?? null;
        $digits = substr($text, 2);

        if ($radix === null || preg_match($radix[0], $digits) !== 1) {
            return NAN;
        }

        $binary = '';

        foreach (str_split(strtolower($digits)) as $digit) {
            $binary .= str_pad(base_convert($digit, $radix[1], 2), $radix[2], '0', STR_PAD_LEFT);
        }

        return self::rounded(ltrim($binary, '0'));
    }

    private static function rounded(string $binary): float
    {
        if (\strlen($binary) <= self::MANTISSA_BITS) {
            return (float) bindec($binary === '' ? '0' : $binary);
        }

        $mantissa = (int) bindec(substr($binary, 0, self::MANTISSA_BITS));
        $rest = substr($binary, self::MANTISSA_BITS);

        if ($rest[0] === '1' && (str_contains(substr($rest, 1), '1') || $mantissa % 2 === 1)) {
            $mantissa++;
        }

        return $mantissa * (2.0 ** (\strlen($binary) - self::MANTISSA_BITS));
    }
}
