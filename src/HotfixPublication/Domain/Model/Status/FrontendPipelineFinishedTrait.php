<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;

trait FrontendPipelineFinishedTrait
{
    public function awaitLatestFrontendPipeline(
        ContinuousIntegrationClientInterface $ciClient,
        HotfixPublicationInterface $context,
        ?\DateInterval $maxAwaitingTime = null,
    ): void {
        // do nothing
    }
}
