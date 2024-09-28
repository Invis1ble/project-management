<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\UpdateExtraDeploymentBranchMergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\StatusProviderInterface;

final readonly class StatusFrontendApplicationBranchSetToDevelopment extends AbstractStatus
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
        HotfixPublicationInterface $context,
    ): void {
        $extraDeploymentBranchName = $updateExtraDeploymentBranchMergeRequestFactory->extraDeploymentBranchName();

        if (null === $extraDeploymentBranchName) {
            $this->setStatusDone($context);

            return;
        }

        $compareResult = $backendSourceCodeRepository->compare(
            from: $extraDeploymentBranchName,
            to: $updateExtraDeploymentBranchMergeRequestFactory->developmentBranchName(),
        );

        if ($compareResult->diffsEmpty()) {
            $this->setStatusDone($context);

            return;
        }

        $mergeRequest = $updateExtraDeploymentBranchMergeRequestFactory->createMergeRequest();

        if (null === $mergeRequest) {
            $this->setStatusDone($context);

            return;
        }

        $next = new StatusMergeRequestIntoExtraDeploymentBranchCreated([
            'project_id' => $mergeRequest->projectId->value(),
            'merge_request_iid' => $mergeRequest->iid->value(),
        ]);

        $this->setPublicationStatus($context, $next);
    }

    public function __toString(): string
    {
        return Dictionary::FrontendApplicationBranchSetToDevelopment->value;
    }

    private function setStatusDone(HotfixPublicationInterface $context): void
    {
        $this->setPublicationStatus($context, new StatusDone());
    }
}
