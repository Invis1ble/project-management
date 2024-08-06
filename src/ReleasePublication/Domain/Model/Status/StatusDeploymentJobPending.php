<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status;

final readonly class StatusDeploymentJobPending extends StatusDeploymentJobAwaitable
{
    public function __toString(): string
    {
        return Dictionary::DeploymentJobPending->value;
    }
}
