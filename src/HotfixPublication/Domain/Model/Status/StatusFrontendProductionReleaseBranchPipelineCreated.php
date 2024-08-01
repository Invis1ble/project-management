<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusFrontendProductionReleaseBranchPipelineCreated extends StatusFrontendProductionReleaseBranchPipelineAwaitable
{
    public function __toString(): string
    {
        return Dictionary::FrontendProductionReleaseBranchPipelineCreated->value;
    }
}
