<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\UpdateExtraDeploymentBranchMergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\StatusProviderInterface;

abstract readonly class StatusTagPipelineRetryable extends StatusTagPipelineNotInProgress
{
    public const int MAX_RETRIES = 2;

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
        $statusContext = $this->context->toArray();
        $retryCounter = $statusContext['retry_counter'] ?? 0;

        if (self::MAX_RETRIES === $retryCounter) {
            return; // failed
        }

        $pipeline = $backendCiClient->retryPipeline(Pipeline\PipelineId::from($statusContext['pipeline_id']));
        $statusContext = ['retry_counter' => $retryCounter + 1] + $statusContext;

        $next = match ($pipeline->status) {
            Pipeline\Status::Created => new StatusTagPipelineCreated($statusContext),
            Pipeline\Status::WaitingForResource => new StatusTagPipelineWaitingForResource($statusContext),
            Pipeline\Status::Preparing => new StatusTagPipelinePreparing($statusContext),
            Pipeline\Status::Pending => new StatusTagPipelinePending($statusContext),
            Pipeline\Status::Running => new StatusTagPipelineRunning($statusContext),
            Pipeline\Status::Success => new StatusTagPipelineSuccess($statusContext),
            Pipeline\Status::Failed => new StatusTagPipelineFailed($statusContext),
            Pipeline\Status::Canceled => new StatusTagPipelineCanceled($statusContext),
            Pipeline\Status::Skipped => new StatusTagPipelineSkipped($statusContext),
            Pipeline\Status::Manual => new StatusTagPipelineManual($statusContext),
            Pipeline\Status::Scheduled => new StatusTagPipelineScheduled($statusContext),
        };

        $this->setPublicationStatus(
            publication: $context,
            status: $next,
        );
    }
}
