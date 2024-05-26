<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Domain\Model\Status;

abstract readonly class StatusFrontendPipelineFinished extends AbstractStatus
{
    use FrontendPipelineFinishedTrait;
}
