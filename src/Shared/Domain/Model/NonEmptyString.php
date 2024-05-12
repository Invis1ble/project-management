<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model;

use JsonSerializable;

readonly class NonEmptyString implements \Stringable, JsonSerializable
{
    protected string $value;

    public function __construct(string $value)
    {
        $this->validate($value);

        $this->value = $value;
    }

    public static function fromString(string $value): static
    {
        return new static($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }

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
