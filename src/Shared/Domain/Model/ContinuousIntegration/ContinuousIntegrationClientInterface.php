<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration;

use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;

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
    ): ?Pipeline\Pipeline;

    /**
     * @param ?\DateInterval $maxAwaitingTime default max awaiting time is 30 min
     * @param ?\DateInterval $tickInterval    default tick interval is 10 sec
     */
    public function awaitJob(
        Job\JobId $jobId,
        ?\DateInterval $maxAwaitingTime = null,
        ?\DateInterval $tickInterval = null,
    ): ?Job\Job;

    public function retryPipeline(Pipeline\PipelineId $pipelineId): ?Pipeline\Pipeline;

    public function retryJob(Job\JobId $jobId): ?Job\Job;

    public function deployOnProduction(Tag\VersionName $tagName): Job\Job;
}
