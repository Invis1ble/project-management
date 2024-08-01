<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusFrontendProductionReleaseBranchPipelinePreparing extends StatusFrontendProductionReleaseBranchPipelineAwaitable
{
    public function __toString(): string
    {
        return Dictionary::FrontendProductionReleaseBranchPipelinePreparing->value;
    }
}
