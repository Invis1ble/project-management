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
        public \DateInterval $maxAwaitingTime,
    ) {
        parent::__construct($branchName);
    }

    public function __unserialize(array $data): void
    {
        parent::__unserialize($data);

        $this->pipelineId = $data['pipeline_id'];
        $this->status = $data['status'];
        $this->projectId = $data['project_id'];
        $this->maxAwaitingTime = $data['max_awaiting_time'];
    }

    public function jsonSerialize(): array
    {
        return [
            'pipeline_id' => $this->pipelineId,
            'status' => $this->status,
            'project_id' => $this->projectId,
            'max_awaiting_time' => $this->maxAwaitingTime,
        ] + parent::jsonSerialize();
    }
}
