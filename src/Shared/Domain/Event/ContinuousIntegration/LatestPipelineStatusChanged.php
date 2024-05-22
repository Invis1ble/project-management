<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Event\ContinuousIntegration;

use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\PipelineId;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;

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
