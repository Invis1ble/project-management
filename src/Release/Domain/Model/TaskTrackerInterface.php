<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Model;

use ReleaseManagement\Release\Domain\Model\TaskTracker\Release;
use ReleaseManagement\Shared\Domain\Model\TaskTrackerInterface as BasicTaskTrackerInterface;

interface TaskTrackerInterface extends BasicTaskTrackerInterface
{
    public function latestRelease(): ?Release;
}
