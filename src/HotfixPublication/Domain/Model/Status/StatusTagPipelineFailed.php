<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusTagPipelineFailed extends StatusTagPipelineRetryable
{
    public function __toString(): string
    {
        return Dictionary::TagPipelineFailed->value;
    }
}
