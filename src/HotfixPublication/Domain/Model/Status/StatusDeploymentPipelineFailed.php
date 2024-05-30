<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusDeploymentPipelineFailed extends StatusDeploymentPipelineRetryable
{
    public function __toString(): string
    {
        return Dictionary::DeploymentPipelineFailed->value;
    }
}
