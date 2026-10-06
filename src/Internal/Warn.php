<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Warn
{
    public static function emit(string $message): void
    {
        if ((\PHP_SAPI === 'cli' || \PHP_SAPI === 'phpdbg') && \defined('STDERR') && \is_resource(\STDERR)) {
            @fwrite(\STDERR, $message . \PHP_EOL);

            return;
        }

        error_log($message);
    }
}
