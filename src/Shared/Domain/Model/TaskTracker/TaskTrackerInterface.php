<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker;

use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;

interface TaskTrackerInterface
{
    public function transitionTo(
        Issue\Key $key,
        Transition\Name $transitionName,
    ): void;

    public function latestRelease(): ?Version\Version;

    public function issuesFromActiveSprint(
        ?string $status = null,
        ?array $types = null,
        Issue\Key ...$keys,
    ): Issue\IssueList;

    public function mergeRequestsRelatedToIssue(Issue\IssueId $issueId): MergeRequestList;

    public function issueTransitions(Issue\Key $key): Transition\TransitionList;
}
