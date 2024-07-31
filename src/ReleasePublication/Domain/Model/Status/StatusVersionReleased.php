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

final readonly class StatusVersionReleased extends AbstractStatus
{
    public function proceedToNext(
        MergeRequest\MergeRequestManagerInterface $mergeRequestManager,
        SourceCodeRepositoryInterface $frontendSourceCodeRepository,
        SourceCodeRepositoryInterface $backendSourceCodeRepository,
        ContinuousIntegrationClientInterface $frontendCiClient,
        ContinuousIntegrationClientInterface $backendCiClient,
        SetFrontendApplicationBranchNameCommitFactoryInterface $setFrontendApplicationBranchNameCommitFactory,
        MergeRequest\UpdateExtraDeployBranchMergeRequestFactoryInterface $updateExtraDeployBranchMergeRequestFactory,
        TaskTrackerInterface $taskTracker,
        \DateInterval $pipelineMaxAwaitingTime,
        \DateInterval $pipelineTickInterval,
        ReleasePublicationInterface $context,
    ): void {
        $releaseBranchName = $context->branchName();
        $developmentBranchName = Branch\Name::fromString('develop');

        $mergeRequest = $mergeRequestManager->createMergeRequest(
            projectId: $frontendSourceCodeRepository->projectId(),
            title: MergeRequest\Title::fromString("Merge branch $releaseBranchName into $developmentBranchName"),
            sourceBranchName: $releaseBranchName,
            targetBranchName: $developmentBranchName,
        );

        $this->setPublicationStatus($context, new StatusFrontendMergeRequestIntoDevelopmentBranchCreated([
            'project_id' => $mergeRequest->projectId->value(),
            'merge_request_iid' => $mergeRequest->iid->value(),
        ]));
    }

    public function __toString(): string
    {
        return Dictionary::VersionReleased->value;
    }
}
