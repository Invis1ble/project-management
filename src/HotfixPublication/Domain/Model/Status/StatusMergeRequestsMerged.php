<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusMergeRequestsMerged extends StatusFrontendPipelineAwaitable
{
    public function __toString(): string
    {
        return Dictionary::MergeRequestsMerged->value;
    }
}
