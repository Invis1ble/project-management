<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model;

// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
readonly class String_ implements \Stringable
{
    // phpcs:enable Squiz.Classes.ValidClassName.NotCamelCaps
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

    protected function validate(string $value): void
    {
    }
}
