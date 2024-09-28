<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\PipelineId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\StatusProviderInterface;

abstract readonly class StatusFrontendProductionReleaseBranchPipelineRetryable extends StatusFrontendProductionReleaseBranchPipelineNotInProgress
{
    public const int MAX_RETRIES = 2;

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
        ReleasePublicationInterface $context,
        \DateInterval $pipelineMaxAwaitingTime,
    ): void {
        $statusContext = $this->context->toArray();
        $retryCounter = $statusContext['retry_counter'] ?? 0;

        if (self::MAX_RETRIES === $retryCounter) {
            return; // failed
        }

        $pipeline = $frontendCiClient->retryPipeline(PipelineId::from($statusContext['pipeline_id']));
        $statusContext = ['retry_counter' => $retryCounter + 1] + $statusContext;

        $next = match ($pipeline->status) {
            Status::Created => new StatusFrontendProductionReleaseBranchPipelineCreated($statusContext),
            Status::WaitingForResource => new StatusFrontendProductionReleaseBranchPipelineWaitingForResource($statusContext),
            Status::Preparing => new StatusFrontendProductionReleaseBranchPipelinePreparing($statusContext),
            Status::Pending => new StatusFrontendProductionReleaseBranchPipelinePending($statusContext),
            Status::Running => new StatusFrontendProductionReleaseBranchPipelineRunning($statusContext),
            Status::Success => new StatusFrontendProductionReleaseBranchPipelineSuccess($statusContext),
            Status::Failed => new StatusFrontendProductionReleaseBranchPipelineFailed($statusContext),
            Status::Canceled => new StatusFrontendProductionReleaseBranchPipelineCanceled($statusContext),
            Status::Skipped => new StatusFrontendProductionReleaseBranchPipelineSkipped($statusContext),
            Status::Manual => new StatusFrontendProductionReleaseBranchPipelineManual($statusContext),
            Status::Scheduled => new StatusFrontendProductionReleaseBranchPipelineScheduled($statusContext),
        };

        $this->setPublicationStatus(
            publication: $context,
            status: $next,
        );
    }
}
