<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\TaskTracker;

use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\TaskTrackerInterface as BasicTaskTrackerInterface;

interface TaskTrackerInterface extends BasicTaskTrackerInterface
{
    public function readyForPublishHotfixes(
        ?iterable $keys,
        ?iterable $statuses,
    ): Issue\IssueList;

    public function transitionHotfixesToDone(Issue\Key ...$keys): void;
}
