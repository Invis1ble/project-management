<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status;

final readonly class StatusDeploymentJobSkipped extends StatusDeploymentJobNotInProgress
{
    public function __toString(): string
    {
        return Dictionary::DeploymentJobSkipped->value;
    }
}
