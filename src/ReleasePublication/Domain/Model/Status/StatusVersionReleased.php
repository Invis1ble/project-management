<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\StatusProviderInterface;

final readonly class StatusVersionReleased extends AbstractStatus
{
    public function proceedToNext(
        MergeRequest\MergeRequestManagerInterface $mergeRequestManager,
        SourceCodeRepositoryInterface $frontendSourceCodeRepository,
        SourceCodeRepositoryInterface $backendSourceCodeRepository,
        ContinuousIntegrationClientInterface $frontendCiClient,
        ContinuousIntegrationClientInterface $backendCiClient,
        SetFrontendApplicationBranchNameCommitFactoryInterface $setFrontendApplicationBranchNameCommitFactory,
        MergeRequest\UpdateExtraDeploymentBranchMergeRequestFactoryInterface $updateExtraDeploymentBranchMergeRequestFactory,
        TaskTrackerInterface $taskTracker,
        StatusProviderInterface $issueStatusProvider,
        \DateInterval $pipelineTickInterval,
        ReleasePublicationInterface $publication,
        \DateInterval $pipelineMaxAwaitingTime,
    ): void {
        $releaseBranchName = $publication->branchName();
        $developmentBranchName = Branch\Name::fromString('develop');

        $compareResult = $frontendSourceCodeRepository->compare(
            from: $developmentBranchName,
            to: $releaseBranchName,
        );

        if ($compareResult->diffsEmpty()) {
            $next = new StatusFrontendDevelopmentBranchSynchronized();
        } else {
            $mergeRequest = $mergeRequestManager->createMergeRequest(
                projectId: $frontendSourceCodeRepository->projectId(),
                title: MergeRequest\Title::fromString("Merge branch $releaseBranchName into $developmentBranchName"),
                sourceBranchName: $releaseBranchName,
                targetBranchName: $developmentBranchName,
            );

            $next = new StatusFrontendMergeRequestIntoDevelopmentBranchCreated([
                'project_id' => $mergeRequest->projectId->value(),
                'merge_request_iid' => $mergeRequest->iid->value(),
            ]);
        }

        $this->setPublicationStatus($publication, $next);
    }

    public function __toString(): string
    {
        return Dictionary::VersionReleased->value;
    }
}
