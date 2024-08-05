<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job\Status;

abstract readonly class AbstractStatus implements StatusInterface
{
    public function finished(): bool
    {
        return false;
    }

    public function inProgress(): bool
    {
        return false;
    }

    public function equals(StatusInterface $other): bool
    {
        return $other::class === static::class;
    }
}
