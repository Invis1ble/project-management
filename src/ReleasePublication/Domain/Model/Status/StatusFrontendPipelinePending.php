<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Domain\Model\Status;

final readonly class StatusFrontendPipelinePending extends StatusFrontendPipelineAwaitable
{
    public function __toString(): string
    {
        return Dictionary::FrontendPipelinePending->value;
    }
}
