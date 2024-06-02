<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusTagPipelineRunning extends StatusTagPipelineAwaitable
{
    public function __toString(): string
    {
        return Dictionary::TagPipelineRunning->value;
    }
}
