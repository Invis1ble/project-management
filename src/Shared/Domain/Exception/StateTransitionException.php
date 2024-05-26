<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Exception;

class StateTransitionException extends ConflictException
{
    public function __construct(?string $message = null, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message ?? 'Invalid state transition.', $code, $previous);
    }
}
