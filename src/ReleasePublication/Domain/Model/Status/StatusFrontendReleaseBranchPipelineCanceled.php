<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status;

final readonly class StatusFrontendReleaseBranchPipelineCanceled extends StatusFrontendReleaseBranchPipelineNotInProgress
{
    public function __toString(): string
    {
        return Dictionary::FrontendReleaseBranchPipelineCanceled->value;
    }
}
