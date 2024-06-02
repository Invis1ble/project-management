<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint;

enum State: string
{
    case Active = 'active';
    case Closed = 'closed';

    public function active(): bool
    {
        return self::Active === $this;
    }
}
