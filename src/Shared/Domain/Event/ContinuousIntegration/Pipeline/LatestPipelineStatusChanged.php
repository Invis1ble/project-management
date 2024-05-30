<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\PipelineId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;

final readonly class LatestPipelineStatusChanged extends AbstractPipelineEvent
{
    public function __construct(
        ProjectId $projectId,
        Ref $ref,
        PipelineId $pipelineId,
        public ?Status $previousStatus,
        Status $status,
        \DateInterval $maxAwaitingTime,
    ) {
        parent::__construct(
            $projectId,
            $ref,
            $pipelineId,
            $status,
            $maxAwaitingTime,
        );
    }
}
