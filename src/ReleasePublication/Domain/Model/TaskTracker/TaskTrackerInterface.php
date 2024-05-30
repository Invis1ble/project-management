<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Domain\Model\TaskTracker;

use ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use ProjectManagement\Shared\Domain\Model\TaskTracker\TaskTrackerInterface as BasicTaskTrackerInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Version\Version;

interface TaskTrackerInterface extends BasicTaskTrackerInterface
{
    public function renameReleaseCandidate(Name $branchName): Version;

    public function createReleaseCandidate(): Version;

    public function readyToMergeTasksInActiveSprint(): IssueList;
}
