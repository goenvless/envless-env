<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Timestamp
{
    private const DAYS = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    public static function now(): string
    {
        $milliseconds = (int) floor(microtime(true) * 1000);

        return gmdate('Y-m-d\TH:i:s', intdiv($milliseconds, 1000)) . \sprintf('.%03dZ', $milliseconds % 1000);
    }

    public static function milliseconds(): float
    {
        return floor(microtime(true) * 1000);
    }

    public static function parse(string $text): ?float
    {
        if (preg_match(Defaults::ISO_TIME, $text, $match, PREG_UNMATCHED_AS_NULL) !== 1 || $match[1] === '-000000') {
            return null;
        }

        [$year, $month, $day] = [(int) $match[1], (int) ($match[2] ?? 1), (int) ($match[3] ?? 1)];
        [$hour, $minute, $second] = [(int) ($match[4] ?? 0), (int) ($match[5] ?? 0), (int) ($match[6] ?? 0)];
        $fraction = (int) str_pad(substr($match[7] ?? '0', 0, 3), 3, '0');

        if ($month < 1 || $month > 12 || $day < 1 || $day > self::days($year, $month) || $minute > 59 || $second > 59 || $hour > 24) {
            return null;
        }

        if ($hour === 24 && ($minute !== 0 || $second !== 0 || $fraction !== 0)) {
            return null;
        }

        $epoch = (new \DateTimeImmutable('@0'))->setDate($year, $month, $day)->setTime($hour, $minute, $second)->getTimestamp();

        return $epoch * 1000.0 + $fraction - self::offset($match, $epoch) * 1000.0;
    }

    /** @param array<array-key, string|null> $match */
    private static function offset(array $match, int $epoch): int
    {
        $zone = $match[8] ?? null;

        if ($zone === 'Z' || ($zone === null && ($match[4] ?? null) === null)) {
            return 0;
        }

        if ($zone === null) {
            return (new \DateTimeImmutable('@' . $epoch))->setTimezone(new \DateTimeZone(date_default_timezone_get()))->getOffset();
        }

        $sign = $zone[0] === '-' ? -1 : 1;

        return $sign * ((int) substr($zone, 1, 2) * 3600 + (int) substr($zone, 4, 2) * 60);
    }

    private static function days(int $year, int $month): int
    {
        $leap = ($year % 4 === 0 && $year % 100 !== 0) || $year % 400 === 0;

        return $month === 2 && $leap ? 29 : self::DAYS[$month - 1];
    }
}
