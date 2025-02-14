<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline;

use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\RefAwareEvent;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\PipelineId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Psr\Http\Message\UriInterface;

abstract readonly class AbstractPipelineEvent extends RefAwareEvent
{
    public function __construct(
        ProjectId $projectId,
        Ref $ref,
        public PipelineId $pipelineId,
        public Status $status,
        public ?UriInterface $guiUrl,
    ) {
        parent::__construct(
            projectId: $projectId,
            ref: $ref,
        );
    }
}
