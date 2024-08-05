<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusDeploymentJobScheduled extends StatusDeploymentJobNotInProgress
{
    public function __toString(): string
    {
        return Dictionary::DeploymentJobScheduled->value;
    }
}
