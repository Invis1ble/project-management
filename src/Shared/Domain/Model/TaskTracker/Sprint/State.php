<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\TaskTracker\Sprint;

enum State: string implements \JsonSerializable
{
    case Active = 'active';
    case Closed = 'closed';

    public function active(): bool
    {
        return self::Active === $this;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
