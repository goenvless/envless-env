<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Home
{
    public static function directory(): ?string
    {
        $value = Environment::filled(\PHP_OS_FAMILY === 'Windows' ? 'USERPROFILE' : 'HOME');

        if ($value !== null) {
            return $value;
        }

        $entry = \function_exists('posix_getpwuid') && \function_exists('posix_geteuid') ? posix_getpwuid(posix_geteuid()) : false;
        $directory = \is_array($entry) ? $entry['dir'] : null;

        return \is_string($directory) && $directory !== '' ? $directory : null;
    }

    public static function cache(): ?CacheRoot
    {
        $home = self::directory();
        $base = $home === null ? null : rtrim($home, '/\\') . '/' . Protocol::CONFIG['DIR'];

        if ($base !== null && self::usable($base)) {
            return new CacheRoot($base, $base . '/' . Protocol::CONFIG['CACHE_DIR']);
        }

        $temp = rtrim(sys_get_temp_dir(), '/\\') . '/' . Defaults::TEMP_DIRECTORY . self::user();

        return self::claimed($temp) ? new CacheRoot($temp, $temp . '/' . Protocol::CONFIG['CACHE_DIR']) : null;
    }

    private static function usable(string $base): bool
    {
        $directory = $base . '/' . Protocol::CONFIG['CACHE_DIR'];

        while (!file_exists($directory) && \dirname($directory) !== $directory) {
            $directory = \dirname($directory);
        }

        return is_dir($directory) && is_writable($directory);
    }

    private static function claimed(string $directory): bool
    {
        if (!is_dir($directory) && !@mkdir($directory, Protocol::FILE_MODES['DIR']) && !is_dir($directory)) {
            return false;
        }

        $owner = \function_exists('posix_geteuid') ? posix_geteuid() : null;

        return !is_link($directory) && is_writable($directory) && ($owner === null || @fileowner($directory) === $owner) && (@fileperms($directory) & 0o077) === 0;
    }

    private static function user(): string
    {
        $user = \function_exists('posix_geteuid') ? (string) posix_geteuid() : get_current_user();

        return (string) preg_replace(Defaults::SLUG_UNSAFE, '_', $user);
    }
}
