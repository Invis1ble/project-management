<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusTagPipelineStuck extends StatusTagPipelineNotInProgress
{
    public function __toString(): string
    {
        return Dictionary::TagPipelineStuck->value;
    }
}
