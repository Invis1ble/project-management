<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\TaskTracker;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\TaskTrackerInterface as BasicTaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version;

interface TaskTrackerInterface extends BasicTaskTrackerInterface
{
    public function transitionTasksToReleaseCandidate(Issue\Key ...$keys): void;

    public function renameReleaseCandidate(Branch\Name $branchName): Version\Version;

    public function createReleaseCandidate(): Version\Version;

    public function releaseVersion(Branch\Name $branchName): Version\Version;

    public function tasksInActiveSprint(Issue\Status ...$statuses): Issue\IssueList;
}
