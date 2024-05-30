<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\TaskTracker;

use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueId;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Key;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Version\Version;

interface TaskTrackerInterface
{
    public function latestRelease(): ?Version;

    public function issuesFromActiveSprint(
        ?string $status = null,
        ?array $types = null,
        Key ...$keys,
    ): IssueList;

    public function mergeRequestsRelatedToIssue(IssueId $issueId): MergeRequestList;

    public function issueTransitions(Key $key): array;
}
