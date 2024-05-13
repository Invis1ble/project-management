<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model;

use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

abstract readonly class AbstractUuid implements IdInterface
{
    protected function __construct(private AbstractUid $uuid)
    {
    }

    public static function fromString(string $value): static
    {
        return new static(Uuid::fromString($value));
    }

    public function __toString(): string
    {
        return (string) $this->uuid;
    }

    public function equals(IdInterface $id): bool
    {
        return $this::class === $id::class && (string) $this->uuid === (string) $id;
    }

    public function serialize(): string
    {
        return (string) $this;
    }

    public function unserialize(string $data): void
    {
        $this->uuid = Uuid::fromString($data[0]);
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
}
