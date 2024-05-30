<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusTagPipelineScheduled extends StatusTagPipelineNotInProgress
{
    public function __toString(): string
    {
        return Dictionary::TagPipelineScheduled->value;
    }
}
