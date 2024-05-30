<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Issue;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class StatusMergeRequestsIntoReleaseCreated extends AbstractStatus
{
    public function proceedToNext(
        MergeRequestManagerInterface $mergeRequestManager,
        SourceCodeRepositoryInterface $frontendSourceCodeRepository,
        SourceCodeRepositoryInterface $backendSourceCodeRepository,
        ContinuousIntegrationClientInterface $frontendCiClient,
        ContinuousIntegrationClientInterface $backendCiClient,
        SetFrontendApplicationBranchNameCommitFactoryInterface $setFrontendApplicationBranchNameCommitFactory,
        TaskTrackerInterface $taskTracker,
        ProjectResolverInterface $projectResolver,
        HotfixPublicationInterface $context,
    ): void {
        $hotfixes = new IssueList(
            ...$context->hotfixes()
                ->map(function (Issue $hotfix) use ($mergeRequestManager): Issue {
                    $mergeRequests = $hotfix->mergeRequestsToMerge->mergeMergeRequests($mergeRequestManager);

                    return $hotfix->withMergeRequestsToMerge($mergeRequests);
                }),
        );

        $this->setPublicationProperty($context, 'hotfixes', $hotfixes);
        $this->setPublicationStatus($context, new StatusMergeRequestsIntoDevelopMerged());
    }

    public function __toString(): string
    {
        return Dictionary::MergeRequestsIntoReleaseCreated->value;
    }
}
