<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;

interface UpdateExtraDeploymentBranchMergeRequestFactoryInterface
{
    public function createMergeRequest(): ?MergeRequest;

    public function extraDeploymentBranchName(): ?Branch\Name;

    public function developmentBranchName(): Branch\Name;
}
