<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job;

use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\ProjectIdAwareEvent;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;

final readonly class JobStuck extends ProjectIdAwareEvent
{
    public function __construct(
        Project\ProjectId $projectId,
        public Job\JobId $jobId,
        public \DateInterval $maxAwaitingTime,
    ) {
        parent::__construct($projectId);
    }
}
