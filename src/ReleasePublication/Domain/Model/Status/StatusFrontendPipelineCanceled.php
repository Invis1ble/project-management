<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Domain\Model\Status;

final readonly class StatusFrontendPipelineCanceled extends StatusFrontendPipelineFinished
{
    public function __toString(): string
    {
        return Dictionary::FrontendPipelineCanceled->value;
    }
}
