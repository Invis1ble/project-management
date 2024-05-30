<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\ContinuousIntegration;

use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job\Job;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Pipeline;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag\VersionName;

interface ContinuousIntegrationClientInterface
{
    /**
     * @param ?\DateInterval $maxAwaitingTime default max awaiting time is 30 min
     * @param ?\DateInterval $tickInterval    default tick interval is 10 sec
     */
    public function awaitLatestPipeline(
        Ref $ref,
        \DateTimeImmutable $createdAfter,
        ?\DateInterval $maxAwaitingTime = null,
        ?\DateInterval $tickInterval = null,
    ): ?Pipeline;

    public function deployOnProduction(VersionName $tagName): Job;
}
