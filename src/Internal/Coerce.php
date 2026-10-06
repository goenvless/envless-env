<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Coerce
{
    public static function by(string $value, string $type): string|int|float|bool
    {
        return match ($type) {
            Protocol::VAR_TYPES['NUMBER'] => self::number($value),
            Protocol::VAR_TYPES['BOOLEAN'] => self::boolean($value),
            default => $value,
        };
    }

    public static function number(string $value): int|float
    {
        $number = Numbers::parse($value);
        $whole = is_finite($number) && $number === floor($number) && abs($number) <= Defaults::SAFE_INTEGER;

        return $whole && fdiv(1, $number) !== -INF ? (int) $number : $number;
    }

    public static function boolean(string $value): bool
    {
        return $value === 'true' || $value === '1';
    }
}
