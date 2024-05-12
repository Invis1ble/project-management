<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\TaskTracker;

use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Issue\IssueId;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

interface TaskTrackerInterface
{
    public function issuesFromActiveSprint(
        string $status = null,
        array $types = null,
    ): IssueList;

    public function mergeRequestsRelatedToIssue(IssueId $issueId): MergeRequestList;
}
