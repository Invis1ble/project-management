<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\UpdateExtraDeploymentBranchMergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\StatusProviderInterface;

abstract readonly class StatusFrontendProductionReleaseBranchPipelineAwaitable extends AbstractStatus
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
        if ($context->containsFrontendMergeRequestToMerge($projectResolver)) {
            $pipeline = $frontendCiClient->awaitLatestPipeline(
                ref: Branch\Name::fromString('master'),
                createdAfter: $context->createdAt(),
                maxAwaitingTime: $pipelineMaxAwaitingTime,
                tickInterval: $pipelineTickInterval,
            );

            $statusContext = $this->context->toArray();
            $statusContext['pipeline_id'] = $pipeline->id->value();

            if (null === $pipeline) {
                $next = new StatusFrontendProductionReleaseBranchPipelineStuck();
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
        } else {
            $next = new StatusFrontendProductionReleaseBranchPipelineSuccess();
        }

        $this->setPublicationStatus($context, $next);
    }
}
