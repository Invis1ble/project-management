<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Model\TaskTracker\Issue;

use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Model\DevelopmentCollaboration\MergeRequest\MapMergeRequestsToMergedTrait;

trait MapMergeRequestsToMergeToMergedTrait
{
    use MapMergeRequestsToMergedTrait;

    public function mapMereRequestsToMergeToMerged(Issue\IssueList $issues): Issue\IssueList
    {
        return new Issue\IssueList(...$issues->map(
            fn (Issue\Issue $issue): Issue\Issue => $issue->withMergeRequestsToMerge(
                $this->mapMereRequestsToMerged($issue->mergeRequestsToMerge),
            ),
        ));
    }
}
