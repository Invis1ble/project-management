<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Model;

interface ReleaseFactoryInterface
{
    public function createRelease(ReleaseBranchName $branchName): ReleaseInterface;
}
