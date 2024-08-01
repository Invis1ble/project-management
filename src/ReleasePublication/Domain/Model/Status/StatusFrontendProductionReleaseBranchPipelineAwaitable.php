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

abstract readonly class StatusFrontendProductionReleaseBranchPipelineAwaitable extends AbstractStatus
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
        $pipeline = $frontendCiClient->awaitLatestPipeline(
            ref: $context->branchName(),
            createdAfter: $context->createdAt(),
            maxAwaitingTime: $pipelineMaxAwaitingTime,
            tickInterval: $pipelineTickInterval,
        );

        $statusContext = $this->context->toArray();
        $statusContext['pipeline_id'] = $pipeline->id->value();

        if (null === $pipeline) {
            $next = new StatusFrontendProductionReleaseBranchPipelineStuck($statusContext);
        } else {
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
        }

        $this->setPublicationStatus($context, $next);
    }
}
