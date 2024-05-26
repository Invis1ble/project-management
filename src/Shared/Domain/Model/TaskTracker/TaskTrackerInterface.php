<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\TaskTracker;

use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueId;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

interface TaskTrackerInterface
{
    public function issuesFromActiveSprint(
        ?string $status = null,
        ?array $types = null,
    ): IssueList;

    public function mergeRequestsRelatedToIssue(IssueId $issueId): MergeRequestList;
}
