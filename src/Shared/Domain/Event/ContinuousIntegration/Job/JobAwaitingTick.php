<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Psr\Http\Message\UriInterface;

final readonly class JobAwaitingTick extends AbstractJobEvent
{
    public function __construct(
        Project\ProjectId $projectId,
        Ref $ref,
        public Pipeline\PipelineId $pipelineId,
        Job\JobId $jobId,
        Job\Name $name,
        Job\Status\StatusInterface $status,
        ?UriInterface $guiUrl,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $startedAt,
        ?\DateTimeImmutable $finishedAt,
        public \DateInterval $maxAwaitingTime,
    ) {
        parent::__construct(
            projectId: $projectId,
            ref: $ref,
            jobId: $jobId,
            name: $name,
            status: $status,
            guiUrl: $guiUrl,
            createdAt: $createdAt,
            startedAt: $startedAt,
            finishedAt: $finishedAt,
        );
    }
}
