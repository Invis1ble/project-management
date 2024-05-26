<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

abstract readonly class StatusFrontendPipelineFinished extends AbstractStatus
{
    use FrontendPipelineFinishedTrait;
}
