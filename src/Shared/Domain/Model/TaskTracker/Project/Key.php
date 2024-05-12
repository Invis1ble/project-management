<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\TaskTracker\Project;

final readonly class Key implements \Stringable
{
    protected string $value;

    public function __construct(string $value)
    {
        $this->validate($value);

        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function validate(string $value): void
    {
        if (!preg_match('/^[A-Z][A-Z]+$/', $value)) {
            throw new \InvalidArgumentException("Invalid project key provided: $value");
        }
    }
}
