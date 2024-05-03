<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Model\Status;

use ReleaseManagement\Release\Domain\Model\ReleaseInterface;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegrationClientInterface;

trait FrontendPipelineFinishedTrait
{
    public function awaitLatestFrontendPipeline(
        ContinuousIntegrationClientInterface $ciClient,
        ReleaseInterface $context,
        \DateInterval $maxAwaitingTime = null,
    ): void {
        // do nothing
    }
}
