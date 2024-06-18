<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\UpdateExtraDeployBranchMergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class StatusHotfixesTransitionedToDone extends AbstractStatus
{
    public function proceedToNext(
        MergeRequestManagerInterface $mergeRequestManager,
        SourceCodeRepositoryInterface $frontendSourceCodeRepository,
        SourceCodeRepositoryInterface $backendSourceCodeRepository,
        ContinuousIntegrationClientInterface $frontendCiClient,
        ContinuousIntegrationClientInterface $backendCiClient,
        SetFrontendApplicationBranchNameCommitFactoryInterface $setFrontendApplicationBranchNameCommitFactory,
        UpdateExtraDeployBranchMergeRequestFactoryInterface $updateExtraDeployBranchMergeRequestFactory,
        TaskTrackerInterface $taskTracker,
        ProjectResolverInterface $projectResolver,
        \DateInterval $pipelineMaxAwaitingTime,
        \DateInterval $pipelineTickInterval,
        HotfixPublicationInterface $context,
    ): void {
        $hasNewMergeRequestToMerge = false;

        $hotfixes = new IssueList(
            ...$context->hotfixes()
                ->map(function (Issue $hotfix) use ($mergeRequestManager, &$hasNewMergeRequestToMerge): Issue {
                    $mergeRequestsToMergeIntoDevelop = $hotfix->mergeRequestsToMerge->createCopiesWithNewTargetBranch(
                        mergeRequestManager: $mergeRequestManager,
                        targetBranchName: Branch\Name::fromString('master'),
                        newTargetBranchName: Branch\Name::fromString('develop'),
                    );

                    if (!$mergeRequestsToMergeIntoDevelop->empty()) {
                        $hasNewMergeRequestToMerge = true;
                    }

                    return $hotfix->withMergeRequestsToMerge(
                        $hotfix->mergeRequestsToMerge->concat($mergeRequestsToMergeIntoDevelop),
                    );
                }),
        );

        if ($hasNewMergeRequestToMerge) {
            $this->setPublicationProperty($context, 'hotfixes', $hotfixes);
            $next = new StatusMergeRequestsIntoDevelopCreated();
        } else {
            $next = new StatusDevelopBranchSynchronized();
        }

        $this->setPublicationStatus($context, $next);
    }

    public function __toString(): string
    {
        return Dictionary::HotfixesTransitionedToDone->value;
    }
}
