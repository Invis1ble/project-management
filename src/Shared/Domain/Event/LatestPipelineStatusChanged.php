<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Event;

use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\PipelineId;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;

final readonly class LatestPipelineStatusChanged extends AbstractPipelineEvent
{
    public function __construct(
        PipelineId $pipelineId,
        Name $branchName,
        public ?Status $previousStatus,
        Status $status,
        ProjectId $projectId,
        \DateInterval $maxAwaitingTime,
    ) {
        parent::__construct(
            $pipelineId,
            $branchName,
            $status,
            $projectId,
            $maxAwaitingTime,
        );
    }
}
