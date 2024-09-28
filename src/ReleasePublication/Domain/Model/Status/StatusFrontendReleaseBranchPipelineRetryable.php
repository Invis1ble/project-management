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

abstract readonly class StatusFrontendReleaseBranchPipelineRetryable extends StatusFrontendReleaseBranchPipelineNotInProgress
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
            Status::Created => new StatusFrontendReleaseBranchPipelineCreated($statusContext),
            Status::WaitingForResource => new StatusFrontendReleaseBranchPipelineWaitingForResource($statusContext),
            Status::Preparing => new StatusFrontendReleaseBranchPipelinePreparing($statusContext),
            Status::Pending => new StatusFrontendReleaseBranchPipelinePending($statusContext),
            Status::Running => new StatusFrontendReleaseBranchPipelineRunning($statusContext),
            Status::Success => new StatusFrontendReleaseBranchPipelineSuccess($statusContext),
            Status::Failed => new StatusFrontendReleaseBranchPipelineFailed($statusContext),
            Status::Canceled => new StatusFrontendReleaseBranchPipelineCanceled($statusContext),
            Status::Skipped => new StatusFrontendReleaseBranchPipelineSkipped($statusContext),
            Status::Manual => new StatusFrontendReleaseBranchPipelineManual($statusContext),
            Status::Scheduled => new StatusFrontendReleaseBranchPipelineScheduled($statusContext),
        };

        $this->setPublicationStatus(
            publication: $context,
            status: $next,
        );
    }
}
