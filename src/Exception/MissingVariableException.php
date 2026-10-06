<?php

declare(strict_types=1);

namespace Envless\Env\Exception;

use Envless\Env\Internal\Messages;

final class MissingVariableException extends EnvException
{
    public function __construct(public readonly string $variable)
    {
        parent::__construct(\sprintf(Messages::MISSING, $variable));
    }
}
