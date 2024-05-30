<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\TaskTracker;

use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Key;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\TaskTrackerInterface as BasicTaskTrackerInterface;

interface TaskTrackerInterface extends BasicTaskTrackerInterface
{
    public function readyForPublishHotfixes(Key ...$keys): IssueList;

    public function transitionHotfixesToDone(Key ...$keys): void;
}
