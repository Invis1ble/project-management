<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model;

readonly class NonEmptyString extends String_
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
