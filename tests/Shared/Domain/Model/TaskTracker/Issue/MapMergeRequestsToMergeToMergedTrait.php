<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Issue;

use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MapMergeRequestsToMergedTrait;

trait MapMergeRequestsToMergeToMergedTrait
{
    use MapMergeRequestsToMergedTrait;

    public function mapMergeRequestsToMergeToMerged(Issue\IssueList $issues): Issue\IssueList
    {
        return new Issue\IssueList(...$issues->map(
            fn (Issue\Issue $issue): Issue\Issue => $issue->withMergeRequestsToMerge(
                $this->mapMergeRequestsToMerged($issue->mergeRequestsToMerge),
            ),
        ));
    }

    public function addCopiesWithNewTargetBranchToMergeRequestsToMerge(
        Issue\IssueList $issues,
        Name $targetBranchName,
        Name $newTargetBranchName,
    ): Issue\IssueList {
        return new Issue\IssueList(...$issues->map(
            fn (Issue\Issue $issue): Issue\Issue => $issue->withMergeRequestsToMerge(
                $this->mapMergeRequestsToMerged($issue->mergeRequestsToMerge)
                    ->concat(new MergeRequestList(...(function () use ($issue, $targetBranchName, $newTargetBranchName): iterable {
                        foreach ($issue->mergeRequestsToMerge as $mr) {
                            if ($mr->targetBranchName->equals($targetBranchName)) {
                                yield new MergeRequest(
                                    id: $mr->id,
                                    title: $mr->title,
                                    projectId: $mr->projectId,
                                    projectName: $mr->projectName,
                                    sourceBranchName: $mr->sourceBranchName,
                                    targetBranchName: $newTargetBranchName,
                                    status: $mr->status,
                                    guiUrl: $mr->guiUrl,
                                    details: $mr->details,
                                );
                            }
                        }
                    })())),
            ),
        ));
    }
}
