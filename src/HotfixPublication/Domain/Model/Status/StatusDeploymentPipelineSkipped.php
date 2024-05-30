<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusDeploymentPipelineSkipped extends StatusDeploymentPipelineNotInProgress
{
    public function __toString(): string
    {
        return Dictionary::DeploymentPipelineSkipped->value;
    }
}
