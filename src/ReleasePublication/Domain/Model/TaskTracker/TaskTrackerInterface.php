<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\TaskTracker;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\TaskTrackerInterface as BasicTaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version\Version;

interface TaskTrackerInterface extends BasicTaskTrackerInterface
{
    public function renameReleaseCandidate(Name $branchName): Version;

    public function createReleaseCandidate(): Version;

    public function readyToMergeTasksInActiveSprint(): IssueList;
}
