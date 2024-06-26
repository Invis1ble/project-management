<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusDeploymentPipelineStuck extends StatusDeploymentPipelineNotInProgress
{
    public function __toString(): string
    {
        return Dictionary::DeploymentPipelineStuck->value;
    }
}
