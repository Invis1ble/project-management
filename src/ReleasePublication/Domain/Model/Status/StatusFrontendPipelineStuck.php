<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Domain\Model\Status;

final readonly class StatusFrontendPipelineStuck extends AbstractStatus
{
    public function __toString(): string
    {
        return Dictionary::FrontendPipelineStuck->value;
    }
}
