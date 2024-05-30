<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
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
        ReleasePublicationInterface $context,
    ): void {
        $pipeline = $frontendCiClient->awaitLatestPipeline(
            ref: $context->branchName(),
            createdAfter: $context->createdAt(),
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

        $this->setPublicationStatus($context, $next);
    }
}
