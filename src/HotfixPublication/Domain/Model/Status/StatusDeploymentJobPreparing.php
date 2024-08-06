<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusDeploymentJobPreparing extends StatusDeploymentJobAwaitable
{
    public function __toString(): string
    {
        return Dictionary::DeploymentJobPreparing->value;
    }
}
