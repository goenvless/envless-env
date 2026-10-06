<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class ApiFailure extends \RuntimeException
{
    public function __construct(string $message, public readonly bool $transient, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
