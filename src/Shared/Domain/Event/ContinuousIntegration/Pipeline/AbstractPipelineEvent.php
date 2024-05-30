<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline;

use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\RefAwareEvent;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\PipelineId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;

abstract readonly class AbstractPipelineEvent extends RefAwareEvent
{
    public function __construct(
        ProjectId $projectId,
        Ref $ref,
        public PipelineId $pipelineId,
        public Status $status,
        public \DateInterval $maxAwaitingTime,
    ) {
        parent::__construct($projectId, $ref);
    }

    public function __unserialize(array $data): void
    {
        parent::__unserialize($data);

        $this->pipelineId = $data['pipeline_id'];
        $this->status = $data['status'];
        $this->maxAwaitingTime = $data['max_awaiting_time'];
    }

    public function jsonSerialize(): array
    {
        return [
            'pipeline_id' => $this->pipelineId,
            'status' => $this->status,
            'max_awaiting_time' => $this->maxAwaitingTime,
        ] + parent::jsonSerialize();
    }
}
