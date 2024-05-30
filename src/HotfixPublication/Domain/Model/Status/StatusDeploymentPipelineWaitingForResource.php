<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusDeploymentPipelineWaitingForResource extends StatusDeploymentPipelineAwaitable
{
    public function __toString(): string
    {
        return Dictionary::DeploymentPipelineWaitingForResource->value;
    }
}
