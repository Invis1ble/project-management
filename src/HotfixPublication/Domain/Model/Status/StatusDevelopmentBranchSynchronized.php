<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\UpdateExtraDeploymentBranchMergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\StatusProviderInterface;

final readonly class StatusDevelopmentBranchSynchronized extends AbstractStatus
{
    public function proceedToNext(
        MergeRequestManagerInterface $mergeRequestManager,
        SourceCodeRepositoryInterface $frontendSourceCodeRepository,
        SourceCodeRepositoryInterface $backendSourceCodeRepository,
        ContinuousIntegrationClientInterface $frontendCiClient,
        ContinuousIntegrationClientInterface $backendCiClient,
        SetFrontendApplicationBranchNameCommitFactoryInterface $setFrontendApplicationBranchNameCommitFactory,
        UpdateExtraDeploymentBranchMergeRequestFactoryInterface $updateExtraDeploymentBranchMergeRequestFactory,
        TaskTrackerInterface $taskTracker,
        ProjectResolverInterface $projectResolver,
        StatusProviderInterface $issueStatusProvider,
        \DateInterval $pipelineMaxAwaitingTime,
        \DateInterval $pipelineTickInterval,
        HotfixPublicationInterface $publication,
    ): void {
        $release = $taskTracker->latestRelease();
        $hasNewMergeRequestToMerge = false;

        if (null === $release || $release->released) {
            $this->setPublicationStatus($publication, new StatusReleaseBranchSynchronized());

            return;
        }

        $hotfixes = new IssueList(
            ...$publication->hotfixes()
                ->map(function (Issue $hotfix) use ($frontendSourceCodeRepository, $projectResolver, $backendSourceCodeRepository, $mergeRequestManager, $release, &$hasNewMergeRequestToMerge): Issue {
                    $releaseBranchName = Branch\Name::fromString((string) $release->name);
                    $productionReleaseBranchName = Branch\Name::fromString('master');

                    $mergeRequestsToMerge = new MergeRequestList(
                        ...$hotfix->mergeRequestsToMerge
                            ->targetToBranch($productionReleaseBranchName)
                            ->onlyShouldBeCopiedWithNewTargetBranch(
                                projectResolver: $projectResolver,
                                frontendSourceCodeRepository: $frontendSourceCodeRepository,
                                backendSourceCodeRepository: $backendSourceCodeRepository,
                                branchName: $releaseBranchName,
                            )
                            ->map(fn (MergeRequest $mergeRequest): MergeRequest => $mergeRequestManager->createMergeRequest(
                                projectId: $mergeRequest->projectId,
                                title: $mergeRequest->title,
                                sourceBranchName: $mergeRequest->sourceBranchName,
                                targetBranchName: $releaseBranchName,
                            )),
                    );

                    if (!$mergeRequestsToMerge->empty()) {
                        $hasNewMergeRequestToMerge = true;
                    }

                    return $hotfix->withMergeRequestsToMerge(
                        $hotfix->mergeRequestsToMerge->concat($mergeRequestsToMerge),
                    );
                }),
        );

        if ($hasNewMergeRequestToMerge) {
            $this->setPublicationProperty($publication, 'hotfixes', $hotfixes);
            $next = new StatusMergeRequestsIntoReleaseBranchCreated();
        } else {
            $next = new StatusReleaseBranchSynchronized();
        }

        $this->setPublicationStatus($publication, $next);
    }

    public function __toString(): string
    {
        return Dictionary::DevelopmentBranchSynchronized->value;
    }
}
