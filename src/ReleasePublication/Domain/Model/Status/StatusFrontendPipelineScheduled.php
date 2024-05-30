<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Domain\Model\Status;

final readonly class StatusFrontendPipelineScheduled extends StatusFrontendPipelineNotInProgress
{
    public function __toString(): string
    {
        return Dictionary::FrontendPipelineScheduled->value;
    }
}
