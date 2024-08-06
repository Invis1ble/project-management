<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job\Status;

final readonly class StatusSkipped extends AbstractStatus
{
    public function __toString(): string
    {
        return Dictionary::Skipped->value;
    }
}
