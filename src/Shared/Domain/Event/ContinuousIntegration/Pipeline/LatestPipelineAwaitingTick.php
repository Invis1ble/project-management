<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;

final readonly class LatestPipelineAwaitingTick extends AbstractPipelineEvent
{
    public function __construct(
        Project\ProjectId $projectId,
        Ref $ref,
        Pipeline\PipelineId $pipelineId,
        Pipeline\Status $status,
        public \DateInterval $maxAwaitingTime,
    ) {
        parent::__construct(
            $projectId,
            $ref,
            $pipelineId,
            $status,
        );
    }
}
