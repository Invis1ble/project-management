<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusDeploymentPipelineCanceled extends StatusDeploymentPipelineNotInProgress
{
    public function __toString(): string
    {
        return Dictionary::DeploymentPipelineCanceled->value;
    }
}
