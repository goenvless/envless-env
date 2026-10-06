<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Unsafe
{
    public static function is(string $name): bool
    {
        $lowered = strtolower($name);

        foreach (Protocol::UNSAFE_PROCESS_ENV_NAMES as $unsafe) {
            if (strtolower($unsafe) === $lowered) {
                return true;
            }
        }

        return false;
    }
}
