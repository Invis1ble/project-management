<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model\Status;

final readonly class StatusFrontendPipelineFailed extends StatusFrontendPipelineFinished
{
    public function __toString(): string
    {
        return Dictionary::FrontendPipelineFailed->value;
    }
}
