<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Domain\Model\Status;

use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;

trait FrontendPipelineFinishedTrait
{
    public function awaitLatestFrontendPipeline(
        ContinuousIntegrationClientInterface $ciClient,
        ReleasePublicationInterface $context,
        \DateInterval $maxAwaitingTime = null,
    ): void {
        // do nothing
    }
}
