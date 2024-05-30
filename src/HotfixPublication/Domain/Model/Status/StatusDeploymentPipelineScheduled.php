<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusDeploymentPipelineScheduled extends StatusDeploymentPipelineNotInProgress
{
    public function __toString(): string
    {
        return Dictionary::DeploymentPipelineScheduled->value;
    }
}
