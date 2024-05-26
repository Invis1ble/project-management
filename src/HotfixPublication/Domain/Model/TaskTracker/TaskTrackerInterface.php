<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\TaskTracker;

use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Key;
use ProjectManagement\Shared\Domain\Model\TaskTracker\TaskTrackerInterface as BasicTaskTrackerInterface;

interface TaskTrackerInterface extends BasicTaskTrackerInterface
{
    public function readyForPublishHotfixes(Key ...$keys): IssueList;
}
