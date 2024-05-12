<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model\Status;

final readonly class StatusFrontendPipelineScheduled extends AbstractStatus
{
    public function __toString(): string
    {
        return Dictionary::FrontendPipelineScheduled->value;
    }
}
