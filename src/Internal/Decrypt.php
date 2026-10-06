<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class Decrypt
{
    /** @param list<Variable> $variables */
    public static function entries(array $variables, Keys $keys): Opened
    {
        $entries = [];

        foreach ($variables as $variable) {
            $value = $variable->value;

            if (!\is_string($value)) {
                continue;
            }

            try {
                $entries[$variable->name] = $value === '' ? '' : $keys->open($value);
            } catch (\Throwable) {
                return new Opened([], $variable->name);
            }
        }

        return new Opened($entries, null);
    }
}
