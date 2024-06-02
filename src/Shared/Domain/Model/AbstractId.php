<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model;

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
