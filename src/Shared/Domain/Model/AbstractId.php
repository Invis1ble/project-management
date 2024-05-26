<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model;

abstract readonly class AbstractId implements IdInterface
{
    private int $value;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(int $value)
    {
        $this->validate($value);

        $this->value = $value;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function from(int $value): static
    {
        return new static($value);
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(IdInterface $id): bool
    {
        return (string) $this === (string) $id && static::class === $id::class;
    }

    public function serialize(): string
    {
        return (string) $this;
    }

    public function unserialize(string $data): void
    {
        $this->value = (int) $data;
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

    /**
     * @throws \InvalidArgumentException
     */
    protected function validate(int $value): void
    {
        $minValue = 1;

        if ($value < $minValue) {
            throw new \InvalidArgumentException(sprintf(
                "%s value cannot be lower than $minValue.",
                static::class,
            ));
        }
    }
}
