<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Envelope
{
    public static function iterations(string $encoded): ?int
    {
        $crypto = Protocol::CRYPTO;

        if (str_starts_with($encoded, $crypto['CIPHERTEXT_VERSION_PREFIX_V2'])) {
            $rest = substr($encoded, \strlen($crypto['CIPHERTEXT_VERSION_PREFIX_V2']));
            $separator = strpos($rest, ':');
            $iterations = $separator === false ? NAN : Numbers::parse(substr($rest, 0, $separator));

            if (!is_finite($iterations) || $iterations !== floor($iterations) || $iterations < $crypto['PBKDF2_ITERATIONS_MIN'] || $iterations > $crypto['PBKDF2_ITERATIONS_MAX']) {
                throw new \UnexpectedValueException(Protocol::UNSUPPORTED_VARIABLE_FORMAT);
            }

            return (int) $iterations;
        }

        if (str_starts_with($encoded, $crypto['CIPHERTEXT_VERSION_PREFIX'])) {
            return null;
        }

        throw new \UnexpectedValueException(Protocol::UNSUPPORTED_VARIABLE_FORMAT);
    }
}
