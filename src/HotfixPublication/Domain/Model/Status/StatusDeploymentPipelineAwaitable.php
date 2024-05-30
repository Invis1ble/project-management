<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

abstract readonly class StatusDeploymentPipelineAwaitable extends AbstractStatus
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
        HotfixPublicationInterface $context,
    ): void {
        $pipeline = $frontendCiClient->awaitLatestPipeline(
            ref: $context->tagName(),
            createdAfter: $context->createdAt(),
        );

        if (null === $pipeline) {
            $next = new StatusDeploymentPipelineStuck();
        } else {
            $next = match ($pipeline->status) {
                Status::Created => new StatusDeploymentPipelineCreated(),
                Status::WaitingForResource => new StatusDeploymentPipelineWaitingForResource(),
                Status::Preparing => new StatusDeploymentPipelinePreparing(),
                Status::Pending => new StatusDeploymentPipelinePending(),
                Status::Running => new StatusDeploymentPipelineRunning(),
                Status::Success => new StatusDeploymentPipelineSuccess(),
                Status::Failed => new StatusDeploymentPipelineFailed(),
                Status::Canceled => new StatusDeploymentPipelineCanceled(),
                Status::Skipped => new StatusDeploymentPipelineSkipped(),
                Status::Manual => new StatusDeploymentPipelineManual(),
                Status::Scheduled => new StatusDeploymentPipelineScheduled(),
            };
        }

        $this->setPublicationStatus($context, $next);
    }
}
