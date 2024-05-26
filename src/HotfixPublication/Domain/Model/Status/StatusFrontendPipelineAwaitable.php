<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequestManagerInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

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
        HotfixPublicationInterface $context,
    ): void {
        $pipeline = $frontendCiClient->awaitLatestPipeline(
            branchName: $context->branchName(),
            createdAfter: $context->createdAt(),
        );

        $status = match ($pipeline['status']) {
            Status::Created->value => new StatusFrontendPipelineCreated(),
            Status::WaitingForResource->value => new StatusFrontendPipelineWaitingForResource(),
            Status::Preparing->value => new StatusFrontendPipelinePreparing(),
            Status::Pending->value => new StatusFrontendPipelinePending(),
            Status::Running->value => new StatusFrontendPipelineRunning(),
            Status::Success->value => new StatusFrontendPipelineSuccess(),
            Status::Failed->value => new StatusFrontendPipelineFailed(),
            Status::Canceled->value => new StatusFrontendPipelineCanceled(),
            Status::Skipped->value => new StatusFrontendPipelineSkipped(),
            Status::Manual->value => new StatusFrontendPipelineManual(),
            Status::Scheduled->value => new StatusFrontendPipelineScheduled(),
            null => new StatusFrontendPipelineStuck(),
        };

        $this->setReleaseStatus($context, $status);
    }
}
