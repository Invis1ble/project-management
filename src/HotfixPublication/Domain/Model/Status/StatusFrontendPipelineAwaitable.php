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
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

abstract readonly class StatusFrontendPipelineAwaitable extends AbstractStatus
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
        if ($context->containsFrontendMergeRequestToMerge($projectResolver)) {
            $pipeline = $frontendCiClient->awaitLatestPipeline(
                ref: Branch\Name::fromString('master'),
                createdAfter: $context->createdAt(),
                maxAwaitingTime: $pipelineMaxAwaitingTime,
                tickInterval: $pipelineTickInterval,
            );

            $statusContext = $this->context->toArray();
            $statusContext['pipeline_id'] = $pipeline->id;

            if (null === $pipeline) {
                $next = new StatusFrontendPipelineStuck();
            } else {
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
            }
        } else {
            $next = new StatusFrontendPipelineSuccess();
        }

        $this->setPublicationStatus($context, $next);
    }
}
