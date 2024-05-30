<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;

interface SetFrontendApplicationBranchNameCommitFactoryInterface
{
    public function createSetFrontendApplicationBranchNameCommit(
        Name $targetBranchName,
        ?Name $startBranchName = null,
    ): ?NewCommit;
}
