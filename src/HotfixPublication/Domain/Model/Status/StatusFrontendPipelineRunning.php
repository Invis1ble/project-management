<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusFrontendPipelineRunning extends StatusFrontendPipelineAwaitable
{
    public function __toString(): string
    {
        return Dictionary::FrontendPipelineRunning->value;
    }
}
