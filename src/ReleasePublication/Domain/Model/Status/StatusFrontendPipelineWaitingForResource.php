<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status;

final readonly class StatusFrontendPipelineWaitingForResource extends StatusFrontendPipelineAwaitable
{
    public function __toString(): string
    {
        return Dictionary::FrontendPipelineWaitingForResource->value;
    }
}
