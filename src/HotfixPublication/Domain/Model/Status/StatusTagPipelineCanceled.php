<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusTagPipelineCanceled extends StatusTagPipelineNotInProgress
{
    public function __toString(): string
    {
        return Dictionary::TagPipelineCanceled->value;
    }
}
