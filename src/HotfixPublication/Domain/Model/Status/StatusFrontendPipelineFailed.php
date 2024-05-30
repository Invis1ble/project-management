<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusFrontendPipelineFailed extends StatusFrontendPipelineRetryable
{
    public function __toString(): string
    {
        return Dictionary::FrontendPipelineFailed->value;
    }
}
