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
}
