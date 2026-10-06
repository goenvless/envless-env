<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

use Envless\Env\Env;

final class UpdateNotice
{
    private static bool $claimed = false;

    private static ?UpdateCheck $check = null;

    public static function check(): void
    {
        if (self::$claimed) {
            return;
        }

        self::$claimed = true;

        try {
            if (!self::isDisabled()) {
                self::$check = UpdateCheck::start();
                register_shutdown_function([self::class, 'finish']);
            }
        } catch (\Throwable) {
            self::$check = null;
        }
    }

    public static function finish(): void
    {
        $check = self::$check;
        self::$check = null;

        try {
            $body = $check !== null && $check->isOwned() ? $check->settled($check->remaining()) : null;
        } catch (\Throwable) {
            $body = null;
        }

        $latest = $body === null ? null : UpdateVersions::latest($body);

        if ($latest !== null && UpdateVersions::isNewer($latest, Env::VERSION) && \defined('STDERR')) {
            @fwrite(\STDERR, self::notice($latest) . \PHP_EOL);
        }
    }

    public static function notice(string $latest): string
    {
        $notice = Protocol::UPDATE_NOTICE;

        return \sprintf(Messages::UPDATE_AVAILABLE, $notice['ICON'], Defaults::PACKAGE, $latest, Env::VERSION, Defaults::UPDATE_NOTICE['COMMAND'], $notice['CHANGELOG_URL']);
    }

    private static function isDisabled(): bool
    {
        if ((\PHP_SAPI !== 'cli' && \PHP_SAPI !== 'phpdbg') || !\extension_loaded('curl') || Environment::filled(Protocol::UPDATE_NOTICE['ENV_DISABLE']) !== null) {
            return true;
        }

        return !\defined('STDOUT') || !\is_resource(\STDOUT) || !stream_isatty(\STDOUT);
    }
}
