<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

use Composer\InstalledVersions;

final class Binding
{
    public readonly string $environment;

    /** @param non-empty-list<string> $environments */
    private function __construct(public readonly string $workspace, public readonly ?string $product, public readonly string $project, public readonly array $environments)
    {
        $this->environment = $environments[0];
    }

    public static function read(): ?self
    {
        foreach (self::directories() as $directory) {
            $path = $directory . '/' . Protocol::ENVLESS_FILE;

            if (file_exists($path)) {
                $file = Json::file($path);

                return $file === null ? null : self::parse($file);
            }
        }

        return null;
    }

    /** @return list<string> */
    private static function directories(): array
    {
        $directories = [];
        $working = getcwd();
        $root = class_exists(InstalledVersions::class) ? realpath(InstalledVersions::getRootPackage()['install_path']) : false;

        foreach ([$working, $root] as $directory) {
            if (\is_string($directory) && !\in_array($directory, $directories, true)) {
                $directories[] = $directory;
            }
        }

        return $directories;
    }

    private static function parse(\stdClass $file): ?self
    {
        $workspace = $file->workspace ?? null;
        $project = $file->project ?? null;
        $environments = $file->environments ?? null;
        $product = $file->product ?? null;

        if (!self::filled($workspace) || !self::filled($project) || !\is_array($environments) || $environments === []) {
            return null;
        }

        foreach ($environments as $environment) {
            if (!self::filled($environment)) {
                return null;
            }
        }

        return new self($workspace, self::filled($product) ? $product : null, $project, array_values($environments));
    }

    /** @phpstan-assert-if-true non-empty-string $value */
    private static function filled(mixed $value): bool
    {
        return \is_string($value) && $value !== '';
    }
}
