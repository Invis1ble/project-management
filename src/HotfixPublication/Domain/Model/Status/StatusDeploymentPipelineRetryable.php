<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\PipelineId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\UpdateExtraDeployBranchMergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

abstract readonly class StatusDeploymentPipelineRetryable extends StatusDeploymentPipelineNotInProgress
{
    public const int MAX_RETRIES = 2;

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
        $statusContext = $this->context->toArray();
        $retryCounter = $statusContext['retry_counter'] ?? 0;

        if (self::MAX_RETRIES === $retryCounter) {
            return; // failed
        }

        $pipeline = $backendCiClient->retryPipeline(PipelineId::from($statusContext['pipeline_id']));
        $statusContext = ['retry_counter' => $retryCounter + 1] + $statusContext;

        $next = match ($pipeline->status) {
            Status::Created => new StatusDeploymentPipelineCreated($statusContext),
            Status::WaitingForResource => new StatusDeploymentPipelineWaitingForResource($statusContext),
            Status::Preparing => new StatusDeploymentPipelinePreparing($statusContext),
            Status::Pending => new StatusDeploymentPipelinePending($statusContext),
            Status::Running => new StatusDeploymentPipelineRunning($statusContext),
            Status::Success => new StatusDeploymentPipelineSuccess($statusContext),
            Status::Failed => new StatusDeploymentPipelineFailed($statusContext),
            Status::Canceled => new StatusDeploymentPipelineCanceled($statusContext),
            Status::Skipped => new StatusDeploymentPipelineSkipped($statusContext),
            Status::Manual => new StatusDeploymentPipelineManual($statusContext),
            Status::Scheduled => new StatusDeploymentPipelineScheduled($statusContext),
        };

        $this->setPublicationStatus(
            publication: $context,
            status: $next,
        );
    }
}
