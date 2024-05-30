<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusDeploymentPipelineCreated extends StatusDeploymentPipelineAwaitable
{
    public function __toString(): string
    {
        return Dictionary::DeploymentPipelineCreated->value;
    }
}
