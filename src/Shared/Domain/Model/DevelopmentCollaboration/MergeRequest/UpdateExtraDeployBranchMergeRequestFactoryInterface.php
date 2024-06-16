<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;

interface UpdateExtraDeployBranchMergeRequestFactoryInterface
{
    public function createMergeRequest(): ?MergeRequest;
}
