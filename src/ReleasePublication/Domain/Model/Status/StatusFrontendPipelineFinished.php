<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model\Status;

abstract readonly class StatusFrontendPipelineFinished extends AbstractStatus
{
    use FrontendPipelineFinishedTrait;
}
