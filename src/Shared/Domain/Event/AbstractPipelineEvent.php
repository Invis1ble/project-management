<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Event;

use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\PipelineId;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;

abstract readonly class AbstractPipelineEvent extends BranchNameAwareEvent
{
    public function __construct(
        public PipelineId $pipelineId,
        Name $branchName,
        public Status $status,
        public ProjectId $projectId,
    ) {
        parent::__construct($branchName);
    }
}
