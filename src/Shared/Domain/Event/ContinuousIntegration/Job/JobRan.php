<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job;

use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\RefAwareEvent;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job\JobId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job\Name;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\PipelineId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;

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
