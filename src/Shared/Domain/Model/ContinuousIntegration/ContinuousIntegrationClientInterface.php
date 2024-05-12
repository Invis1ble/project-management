<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\ContinuousIntegration;

use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;

interface ContinuousIntegrationClientInterface
{
    /**
     * @param ?\DateInterval $maxAwaitingTime default max awaiting time is 30 min
     * @param ?\DateInterval $tickInterval default tick interval is 10 sec
     *
     * @return array{
     *     status: string|null,
     * }
     */
    public function awaitLatestPipeline(
        Name $branchName,
        \DateTimeImmutable $createdAfter,
        \DateInterval $maxAwaitingTime = null,
        \DateInterval $tickInterval = null,
    ): array;

    public function retryLatestPipeline(

    );
}
