<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;

enum Status: string implements \JsonSerializable
{
    case Open = 'OPEN';

    case Merged = 'MERGED';

    case Declined = 'DECLINED';

    public function open(): bool
    {
        return self::Open === $this;
    }

    public function merged(): bool
    {
        return self::Merged === $this;
    }

    public function declined(): bool
    {
        return self::Declined === $this;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
