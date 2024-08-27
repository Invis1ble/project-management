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

final readonly class StatusReleaseCandidateCreated extends AbstractStatus
{
    public function prepared(): bool
    {
        return true;
    }

    public function proceedToNext(
        MergeRequest\MergeRequestManagerInterface $mergeRequestManager,
        SourceCodeRepositoryInterface $frontendSourceCodeRepository,
        SourceCodeRepositoryInterface $backendSourceCodeRepository,
        ContinuousIntegrationClientInterface $frontendCiClient,
        ContinuousIntegrationClientInterface $backendCiClient,
        SetFrontendApplicationBranchNameCommitFactoryInterface $setFrontendApplicationBranchNameCommitFactory,
        MergeRequest\UpdateExtraDeploymentBranchMergeRequestFactoryInterface $updateExtraDeploymentBranchMergeRequestFactory,
        TaskTrackerInterface $taskTracker,
        \DateInterval $pipelineMaxAwaitingTime,
        \DateInterval $pipelineTickInterval,
        ReleasePublicationInterface $context,
    ): void {
        $sourceBranchName = $context->branchName();
        $targetBranchName = Branch\Name::fromString('master');

        $mergeRequest = $mergeRequestManager->createMergeRequest(
            projectId: $frontendSourceCodeRepository->projectId(),
            title: MergeRequest\Title::fromString("Merge branch $sourceBranchName into $targetBranchName"),
            sourceBranchName: $sourceBranchName,
            targetBranchName: $targetBranchName,
        );

        $this->setPublicationStatus($context, new StatusFrontendMergeRequestIntoProductionReleaseBranchCreated([
            'project_id' => $mergeRequest->projectId->value(),
            'merge_request_iid' => $mergeRequest->iid->value(),
        ]));
    }

    public function __toString(): string
    {
        return Dictionary::ReleaseCandidateCreated->value;
    }
}
