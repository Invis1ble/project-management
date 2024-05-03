<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Event;

use ReleaseManagement\Shared\Domain\Model\BranchName;
use ReleaseManagement\Shared\Domain\Model\Pipeline\PipelineId;
use ReleaseManagement\Shared\Domain\Model\Pipeline\Status;
use ReleaseManagement\Shared\Domain\Model\ProjectId;

abstract readonly class AbstractPipelineEvent extends BranchNameAwareEvent
{
    public function __construct(
        public PipelineId $pipelineId,
        BranchName $branchName,
        public Status $status,
        public ProjectId $projectId,
    ) {
        parent::__construct($branchName);
    }
}
