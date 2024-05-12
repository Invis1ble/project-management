<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model\Status;

final readonly class StatusFrontendPipelineSkipped extends StatusFrontendPipelineFinished
{
    public function __toString(): string
    {
        return Dictionary::FrontendPipelineSkipped->value;
    }
}
