<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Model\Status;

abstract readonly class StatusFrontendPipelineFinished extends AbstractStatus
{
    use FrontendPipelineFinishedTrait;
}
