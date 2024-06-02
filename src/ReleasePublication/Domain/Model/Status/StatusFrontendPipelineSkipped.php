<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status;

final readonly class StatusFrontendPipelineSkipped extends StatusFrontendPipelineNotInProgress
{
    public function __toString(): string
    {
        return Dictionary::FrontendPipelineSkipped->value;
    }
}
