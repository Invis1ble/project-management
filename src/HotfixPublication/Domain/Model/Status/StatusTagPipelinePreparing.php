<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusTagPipelinePreparing extends StatusTagPipelineAwaitable
{
    public function __toString(): string
    {
        return Dictionary::TagPipelinePreparing->value;
    }
}
