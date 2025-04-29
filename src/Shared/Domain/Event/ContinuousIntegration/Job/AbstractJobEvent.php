<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job;

use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\RefAwareEvent;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Psr\Http\Message\UriInterface;

abstract readonly class AbstractJobEvent extends RefAwareEvent
{
    public function __construct(
        Project\ProjectId $projectId,
        Ref $ref,
        public Job\JobId $jobId,
        public Job\Name $name,
        public Job\Status\StatusInterface $status,
        public ?UriInterface $guiUrl,
        public \DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $startedAt,
        public ?\DateTimeImmutable $finishedAt,
    ) {
        parent::__construct(
            projectId: $projectId,
            ref: $ref,
        );
    }
}
