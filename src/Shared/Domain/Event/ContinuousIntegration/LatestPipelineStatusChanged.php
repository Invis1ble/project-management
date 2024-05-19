<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Event\ContinuousIntegration;

use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\PipelineId;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;

final readonly class LatestPipelineStatusChanged extends AbstractPipelineEvent
{
    public function __construct(
        ProjectId $projectId,
        Name $branchName,
        PipelineId $pipelineId,
        public ?Status $previousStatus,
        Status $status,
        \DateInterval $maxAwaitingTime,
    ) {
        parent::__construct(
            $projectId,
            $branchName,
            $pipelineId,
            $status,
            $maxAwaitingTime,
        );
    }
}
