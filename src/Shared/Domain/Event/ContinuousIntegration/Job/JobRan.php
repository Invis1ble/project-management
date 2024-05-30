<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job;

use ProjectManagement\Shared\Domain\Event\SourceCodeRepository\RefAwareEvent;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job\JobId;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job\Name;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\PipelineId;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;

final readonly class JobRan extends RefAwareEvent
{
    public function __construct(
        ProjectId $projectId,
        Ref $ref,
        public PipelineId $pipelineId,
        public JobId $jobId,
        public Name $name,
        public \DateTimeImmutable $createdAt,
    ) {
        parent::__construct(
            $projectId,
            $ref,
        );
    }
}
