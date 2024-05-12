<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model\TaskTracker;

use ReleaseManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\TaskTrackerInterface as BasicTaskTrackerInterface;

interface TaskTrackerInterface extends BasicTaskTrackerInterface
{
    public function latestRelease(): ?Release;

    public function readyToMergeTasksInActiveSprint(): IssueList;
}
