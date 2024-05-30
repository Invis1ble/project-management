<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;

final readonly class Key implements \Stringable, \JsonSerializable
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

    public function toBranchName(): Name
    {
        return Name::fromString($this->value);
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
    private function validate(string $value): void
    {
        if (!preg_match('/^[A-Z][A-Z]+-[0-9]+$/', $value)) {
            throw new \InvalidArgumentException("Invalid issue key provided: $value");
        }
    }
}
