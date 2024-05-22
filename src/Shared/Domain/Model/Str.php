<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model;

readonly class Str implements \Stringable, \Serializable, \JsonSerializable
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

    public function serialize(): string
    {
        return $this->value;
    }

    public function unserialize(string $data): void
    {
        $this->value = $data;
    }

    public function __serialize(): array
    {
        return [$this->serialize()];
    }

    public function __unserialize(array $data): void
    {
        $this->unserialize($data[0]);
    }

    public function jsonSerialize(): string
    {
        return $this->serialize();
    }

    protected function validate(string $value): void
    {
    }
}
