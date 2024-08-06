<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job\Status;

final readonly class StatusCanceled extends AbstractStatus
{
    public function finished(): bool
    {
        return true;
    }

    public function __toString(): string
    {
        return Dictionary::Canceled->value;
    }
}
