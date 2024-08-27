<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

abstract readonly class StatusFrontendReleaseBranchPipelineAwaitable extends AbstractStatus
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
        \DateInterval $pipelineMaxAwaitingTime,
        \DateInterval $pipelineTickInterval,
        ReleasePublicationInterface $context,
    ): void {
        $pipeline = $frontendCiClient->awaitLatestPipeline(
            ref: $context->branchName(),
            createdAfter: $context->createdAt(),
            maxAwaitingTime: $pipelineMaxAwaitingTime,
            tickInterval: $pipelineTickInterval,
        );

        $statusContext = $this->context->toArray();
        $statusContext['pipeline_id'] = $pipeline->id->value();

        if (null === $pipeline) {
            $next = new StatusFrontendReleaseBranchPipelineStuck($statusContext);
        } else {
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
        }

        $this->setPublicationStatus($context, $next);
    }
}
