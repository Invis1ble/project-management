<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Status;

use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Status;

final readonly class StatusNotOpen extends AbstractStatus
{
    public function toTaskTrackerStatus(): Status
    {
        return Status::Merged;
    }

    public function __toString(): string
    {
        return Dictionary::NotOpen->value;
    }
}
