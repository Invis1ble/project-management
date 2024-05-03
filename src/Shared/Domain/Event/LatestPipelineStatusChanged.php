<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Event;

use ReleaseManagement\Shared\Domain\Model\BranchName;
use ReleaseManagement\Shared\Domain\Model\Pipeline\PipelineId;
use ReleaseManagement\Shared\Domain\Model\Pipeline\Status;
use ReleaseManagement\Shared\Domain\Model\ProjectId;

final readonly class LatestPipelineStatusChanged extends AbstractPipelineEvent
{
    public function __construct(
        PipelineId $pipelineId,
        BranchName $branchName,
        public ?Status $previousStatus,
        Status $status,
        ProjectId $projectId,
    ) {
        parent::__construct(
            $pipelineId,
            $branchName,
            $status,
            $projectId,
        );
    }
}
