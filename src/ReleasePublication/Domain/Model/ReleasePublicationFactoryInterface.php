<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Domain\Model;

use ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

interface ReleasePublicationFactoryInterface
{
    public function createReleasePublication(
        Name $branchName,
        IssueList $readyToMergeTasks,
    ): ReleasePublicationInterface;
}
