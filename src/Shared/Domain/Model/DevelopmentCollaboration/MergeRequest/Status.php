<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;

enum Status: string
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

    public function equals(self $other): bool
    {
        return $other === $this;
    }
}
