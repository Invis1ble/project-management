<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model\Status;

final readonly class StatusFrontendPipelineManual extends AbstractStatus
{
    public function __toString(): string
    {
        return Dictionary::FrontendPipelineManual->value;
    }
}
