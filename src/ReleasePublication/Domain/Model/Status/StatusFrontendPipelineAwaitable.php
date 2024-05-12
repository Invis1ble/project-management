<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model\Status;

use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;

abstract readonly class StatusFrontendPipelineAwaitable extends AbstractStatus
{
    public function awaitLatestFrontendPipeline(
        ContinuousIntegrationClientInterface $ciClient,
        ReleasePublicationInterface $context,
        \DateInterval $maxAwaitingTime = null,
    ): void {
        $pipeline = $ciClient->awaitLatestPipeline(
            branchName: $context->branchName(),
            createdAfter: $context->createdAt(),
            maxAwaitingTime: $maxAwaitingTime,
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
