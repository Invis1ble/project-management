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

abstract readonly class StatusTagPipelineAwaitable extends AbstractStatus
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
        $pipeline = $backendCiClient->awaitLatestPipeline(
            ref: $context->tagName(),
            createdAfter: $context->createdAt(),
        );

        if (null === $pipeline) {
            $next = new StatusTagPipelineStuck();
        } else {
            $next = match ($pipeline->status) {
                Status::Created => new StatusTagPipelineCreated(),
                Status::WaitingForResource => new StatusTagPipelineWaitingForResource(),
                Status::Preparing => new StatusTagPipelinePreparing(),
                Status::Pending => new StatusTagPipelinePending(),
                Status::Running => new StatusTagPipelineRunning(),
                Status::Success => new StatusTagPipelineSuccess(),
                Status::Failed => new StatusTagPipelineFailed(),
                Status::Canceled => new StatusTagPipelineCanceled(),
                Status::Skipped => new StatusTagPipelineSkipped(),
                Status::Manual => new StatusTagPipelineManual(),
                Status::Scheduled => new StatusTagPipelineScheduled(),
            };
        }

        $this->setPublicationStatus($context, $next);
    }
}
