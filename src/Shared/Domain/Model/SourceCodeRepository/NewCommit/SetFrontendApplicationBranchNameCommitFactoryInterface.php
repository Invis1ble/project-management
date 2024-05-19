<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit;

use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;

interface SetFrontendApplicationBranchNameCommitFactoryInterface
{
    public function createSetFrontendApplicationBranchNameCommit(
        Name $targetBranchName,
        ?Name $startBranchName = null,
    ): NewCommit;
}
