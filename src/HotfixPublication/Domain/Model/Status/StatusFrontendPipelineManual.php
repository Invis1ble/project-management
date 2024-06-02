<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status;

final readonly class StatusFrontendPipelineManual extends StatusFrontendPipelineNotInProgress
{
    public function __toString(): string
    {
        return Dictionary::FrontendPipelineManual->value;
    }
}
