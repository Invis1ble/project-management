<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model\TaskTracker;

use ReleaseManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\TaskTrackerInterface as BasicTaskTrackerInterface;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Version\Version;

interface TaskTrackerInterface extends BasicTaskTrackerInterface
{
    public function renameReleaseCandidate(Name $branchName): Version;

    public function createReleaseCandidate(): Version;

    public function latestRelease(): ?Version;

    public function readyToMergeTasksInActiveSprint(): IssueList;
}
