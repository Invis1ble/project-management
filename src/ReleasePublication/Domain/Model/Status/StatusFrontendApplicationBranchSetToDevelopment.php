<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\StatusProviderInterface;

final readonly class StatusFrontendApplicationBranchSetToDevelopment extends AbstractStatus
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
        $extraDeploymentBranchName = $updateExtraDeploymentBranchMergeRequestFactory->extraDeploymentBranchName();

        if (null === $extraDeploymentBranchName) {
            $this->setPublicationStatus($publication, new StatusDone());

            return;
        }

        $compareResult = $backendSourceCodeRepository->compare(
            from: $extraDeploymentBranchName,
            to: $updateExtraDeploymentBranchMergeRequestFactory->developmentBranchName(),
        );

        if ($compareResult->diffsEmpty()) {
            $this->setPublicationStatus($publication, new StatusDone());

            return;
        }

        $mergeRequest = $updateExtraDeploymentBranchMergeRequestFactory->createMergeRequest();

        if (null === $mergeRequest) {
            $next = new StatusDone();
        } else {
            $next = new StatusMergeRequestIntoExtraDeploymentBranchCreated([
                'project_id' => $mergeRequest->projectId->value(),
                'merge_request_iid' => $mergeRequest->iid->value(),
            ]);
        }

        $this->setPublicationStatus($publication, $next);
    }

    public function __toString(): string
    {
        return Dictionary::FrontendApplicationBranchSetToDevelopment->value;
    }
}
