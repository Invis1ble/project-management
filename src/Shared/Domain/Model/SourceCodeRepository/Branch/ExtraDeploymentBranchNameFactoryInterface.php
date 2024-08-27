<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;

interface ExtraDeploymentBranchNameFactoryInterface
{
    public function createExtraDeploymentBranchName(): ?Name;
}
