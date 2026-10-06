<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class UpdateVersions
{
    public static function latest(string $body): ?string
    {
        $data = json_decode($body, true);
        $versions = \is_array($data) && \is_array($data['packages'] ?? null) ? ($data['packages'][Defaults::PACKAGE] ?? null) : null;
        $best = null;

        foreach (\is_array($versions) ? $versions : [] as $entry) {
            $version = \is_array($entry) ? ($entry['version'] ?? null) : null;

            if (\is_string($version) && self::parts($version) !== null && ($best === null || self::isNewer($version, $best))) {
                $best = $version;
            }
        }

        return $best === null ? null : ltrim(trim($best), 'v');
    }

    public static function isNewer(string $candidate, string $current): bool
    {
        $left = self::parts($candidate);
        $right = self::parts($current);

        return $left !== null && $right !== null && ($left <=> $right) === 1;
    }

    /** @return list<int>|null */
    public static function parts(string $value): ?array
    {
        $pieces = explode('.', ltrim(trim($value), 'v'));

        if (\count($pieces) !== 3 || preg_grep('/\A\d+\z/', $pieces, PREG_GREP_INVERT) !== []) {
            return null;
        }

        return array_map(\intval(...), $pieces);
    }
}
