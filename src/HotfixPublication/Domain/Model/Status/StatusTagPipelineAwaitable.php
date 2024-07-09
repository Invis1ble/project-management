<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\UpdateExtraDeployBranchMergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

abstract readonly class StatusTagPipelineAwaitable extends AbstractStatus
{
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
        $pipeline = $backendCiClient->awaitLatestPipeline(
            ref: $context->tagName(),
            createdAfter: $context->createdAt(),
            maxAwaitingTime: $pipelineMaxAwaitingTime,
            tickInterval: $pipelineTickInterval,
        );

        $statusContext = $this->context->toArray();
        $statusContext['pipeline_id'] = $pipeline->id;

        if (null === $pipeline) {
            $next = new StatusTagPipelineStuck($statusContext);
        } else {
            $next = match ($pipeline->status) {
                Status::Created => new StatusTagPipelineCreated($statusContext),
                Status::WaitingForResource => new StatusTagPipelineWaitingForResource($statusContext),
                Status::Preparing => new StatusTagPipelinePreparing($statusContext),
                Status::Pending => new StatusTagPipelinePending($statusContext),
                Status::Running => new StatusTagPipelineRunning($statusContext),
                Status::Success => new StatusTagPipelineSuccess($statusContext),
                Status::Failed => new StatusTagPipelineFailed($statusContext),
                Status::Canceled => new StatusTagPipelineCanceled($statusContext),
                Status::Skipped => new StatusTagPipelineSkipped($statusContext),
                Status::Manual => new StatusTagPipelineManual($statusContext),
                Status::Scheduled => new StatusTagPipelineScheduled($statusContext),
            };
        }

        $this->setPublicationStatus($context, $next);
    }
}
