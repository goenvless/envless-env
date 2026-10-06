<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Cache
{
    public static function read(Target $target): ?Cached
    {
        $root = Home::cache();
        $path = $root === null ? null : $root->cache . '/' . CacheFile::name($target);
        $file = $path !== null && file_exists($path) ? Json::file($path) : null;

        if ($file === null || !\is_array($file->variables ?? null)) {
            return null;
        }

        $variables = [];

        foreach ($file->variables as $entry) {
            $variable = Variable::fromCache($entry);

            if ($variable === null) {
                return null;
            }

            $variables[] = $variable;
        }

        $cachedAt = $file->cachedAt ?? null;
        $workspace = $file->workspace ?? null;

        if ((\is_string($workspace) ? $workspace : null) !== $target->workspace) {
            return null;
        }

        return new Cached($variables, \is_string($cachedAt) ? $cachedAt : '');
    }

    /** @param list<Variable> $variables */
    public static function write(Target $target, array $variables): void
    {
        $root = Home::cache();

        if ($root === null) {
            return;
        }

        try {
            self::store($root, $root->cache . '/' . CacheFile::name($target), Json::pretty([
                'variables' => array_map(static fn(Variable $variable): array => $variable->cached(), $variables),
                'cachedAt' => Timestamp::now(),
                ...($target->workspace === null ? [] : ['workspace' => $target->workspace]),
            ]));
        } catch (\Throwable) {
        }
    }

    private static function store(CacheRoot $root, string $path, string $json): void
    {
        $directory = Protocol::FILE_MODES['DIR'];

        if (!is_dir($root->cache) && !@mkdir($root->cache, $directory, true) && !is_dir($root->cache)) {
            return;
        }

        if (!@chmod($root->base, $directory) || !@chmod($root->cache, $directory) || !is_writable($root->cache)) {
            return;
        }

        $temporary = @tempnam($root->cache, '.envless-');

        if ($temporary === false) {
            return;
        }

        if (realpath(\dirname($temporary)) !== realpath($root->cache)) {
            @unlink($temporary);

            return;
        }

        if (@file_put_contents($temporary, $json) === false || !@chmod($temporary, Protocol::FILE_MODES['FILE']) || !@rename($temporary, $path)) {
            @unlink($temporary);

            return;
        }

        @chmod($path, Protocol::FILE_MODES['FILE']);
    }
}
