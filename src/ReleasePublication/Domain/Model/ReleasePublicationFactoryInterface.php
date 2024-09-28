<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

interface ReleasePublicationFactoryInterface
{
    public function createReleasePublication(
        Name $branchName,
        IssueList $tasks,
    ): ReleasePublicationInterface;
}
