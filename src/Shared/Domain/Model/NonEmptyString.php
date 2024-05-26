<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model;

readonly class NonEmptyString extends Str
{
    /**
     * @throws \InvalidArgumentException
     */
    protected function validate(string $value): void
    {
        if ('' === $value) {
            throw new \InvalidArgumentException('Value must be a non-empty string.');
        }
    }
}
