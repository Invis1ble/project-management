<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Infrastructure\Domain\Model;

use ReleaseManagement\Release\Domain\Model\ReleaseBranchName;
use ReleaseManagement\Release\Domain\Model\ReleaseFactoryInterface;
use ReleaseManagement\Release\Domain\Model\ReleaseInterface;
use ReleaseManagement\Release\Infrastructure\Domain\Model\Entity\Release;

final readonly class ReleaseFactory implements ReleaseFactoryInterface
{
    public function createRelease(ReleaseBranchName $branchName): ReleaseInterface
    {
        return Release::create($branchName);
    }
}
