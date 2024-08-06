<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job\Status;

final readonly class StatusPreparing extends AbstractStatus
{
    public function inProgress(): bool
    {
        return true;
    }

    public function __toString(): string
    {
        return Dictionary::Preparing->value;
    }
}
