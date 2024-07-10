<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\PipelineId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

abstract readonly class StatusFrontendPipelineRetryable extends StatusFrontendPipelineNotInProgress
{
    public const int MAX_RETRIES = 2;

    public function proceedToNext(
        MergeRequestManagerInterface $mergeRequestManager,
        SourceCodeRepositoryInterface $frontendSourceCodeRepository,
        SourceCodeRepositoryInterface $backendSourceCodeRepository,
        ContinuousIntegrationClientInterface $frontendCiClient,
        ContinuousIntegrationClientInterface $backendCiClient,
        SetFrontendApplicationBranchNameCommitFactoryInterface $setFrontendApplicationBranchNameCommitFactory,
        TaskTrackerInterface $taskTracker,
        \DateInterval $pipelineMaxAwaitingTime,
        \DateInterval $pipelineTickInterval,
        ReleasePublicationInterface $context,
    ): void {
        $statusContext = $this->context->toArray();
        $retryCounter = $statusContext['retry_counter'] ?? 0;

        if (self::MAX_RETRIES === $retryCounter) {
            return; // failed
        }

        $pipeline = $frontendCiClient->retryPipeline(PipelineId::from($statusContext['pipeline_id']));
        $statusContext = ['retry_counter' => $retryCounter + 1] + $statusContext;

        $next = match ($pipeline->status) {
            Status::Created => new StatusFrontendPipelineCreated($statusContext),
            Status::WaitingForResource => new StatusFrontendPipelineWaitingForResource($statusContext),
            Status::Preparing => new StatusFrontendPipelinePreparing($statusContext),
            Status::Pending => new StatusFrontendPipelinePending($statusContext),
            Status::Running => new StatusFrontendPipelineRunning($statusContext),
            Status::Success => new StatusFrontendPipelineSuccess($statusContext),
            Status::Failed => new StatusFrontendPipelineFailed($statusContext),
            Status::Canceled => new StatusFrontendPipelineCanceled($statusContext),
            Status::Skipped => new StatusFrontendPipelineSkipped($statusContext),
            Status::Manual => new StatusFrontendPipelineManual($statusContext),
            Status::Scheduled => new StatusFrontendPipelineScheduled($statusContext),
        };

        $this->setPublicationStatus(
            publication: $context,
            status: $next,
        );
    }
}
