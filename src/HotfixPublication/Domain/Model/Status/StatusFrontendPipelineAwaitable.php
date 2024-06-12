<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
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

            if (null === $pipeline) {
                $next = new StatusFrontendPipelineStuck();
            } else {
                $next = match ($pipeline->status) {
                    Status::Created => new StatusFrontendPipelineCreated(),
                    Status::WaitingForResource => new StatusFrontendPipelineWaitingForResource(),
                    Status::Preparing => new StatusFrontendPipelinePreparing(),
                    Status::Pending => new StatusFrontendPipelinePending(),
                    Status::Running => new StatusFrontendPipelineRunning(),
                    Status::Success => new StatusFrontendPipelineSuccess(),
                    Status::Failed => new StatusFrontendPipelineFailed(),
                    Status::Canceled => new StatusFrontendPipelineCanceled(),
                    Status::Skipped => new StatusFrontendPipelineSkipped(),
                    Status::Manual => new StatusFrontendPipelineManual(),
                    Status::Scheduled => new StatusFrontendPipelineScheduled(),
                };
            }
        } else {
            $next = new StatusFrontendPipelineSuccess();
        }

        if ($next->equals(new StatusFrontendPipelineSuccess())) {
            return;
        }

        $this->setPublicationStatus($context, $next);
    }
}
