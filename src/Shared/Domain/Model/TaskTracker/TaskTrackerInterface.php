<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker;

use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Key;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version\Version;

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
