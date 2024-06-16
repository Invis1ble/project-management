<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

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
        \DateInterval $pipelineMaxAwaitingTime,
        \DateInterval $pipelineTickInterval,
        HotfixPublicationInterface $context,
    ): void {
        $hotfixes = new IssueList(
            ...$context->hotfixes()
                ->map(function (Issue $hotfix) use ($mergeRequestManager): Issue {
                    $mergeRequests = $hotfix->mergeRequestsToMerge->mergeMergeRequests($mergeRequestManager);

                    return $hotfix->withMergeRequestsToMerge($mergeRequests);
                }),
        );

        return;

        $this->setPublicationProperty($context, 'hotfixes', $hotfixes);
        $this->setPublicationStatus($context, new StatusReleaseBranchSynchronized());
    }

    public function __toString(): string
    {
        return Dictionary::MergeRequestsIntoReleaseCreated->value;
    }
}
