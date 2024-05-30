<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Issue;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class StatusDevelopBranchSynchronized extends AbstractStatus
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
        $release = $taskTracker->latestRelease();
        $hasNewMergeRequestToMerge = false;

        if (null !== $release && !$release->released) {
            $hotfixes = new IssueList(
                ...$context->hotfixes()
                    ->map(function (Issue $hotfix) use ($mergeRequestManager, $release, &$hasNewMergeRequestToMerge): Issue {
                        $mergeRequestsToMergeIntoRelease = $hotfix->mergeRequestsToMerge->createCopiesWithNewTargetBranch(
                            mergeRequestManager: $mergeRequestManager,
                            targetBranchName: Branch\Name::fromString('master'),
                            newTargetBranchName: Branch\Name::fromString((string) $release->name),
                        );

                        if (!$mergeRequestsToMergeIntoRelease->empty()) {
                            $hasNewMergeRequestToMerge = true;
                        }

                        return $hotfix->withMergeRequestsToMerge(
                            $hotfix->mergeRequestsToMerge->concat($mergeRequestsToMergeIntoRelease),
                        );
                    }),
            );
        }

        if (isset($hotfixes) && $hasNewMergeRequestToMerge) {
            $this->setPublicationProperty($context, 'hotfixes', $hotfixes);
            $next = new StatusMergeRequestsIntoReleaseCreated();
        } else {
            $next = new StatusReleaseBranchSynchronized();
        }

        $this->setPublicationStatus($context, $next);
    }

    public function __toString(): string
    {
        return Dictionary::DevelopBranchSynchronized->value;
    }
}
