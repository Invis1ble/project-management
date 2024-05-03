<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Model\Status;

final readonly class StatusFrontendPipelineRunning extends StatusFrontendPipelineAwaitable
{
    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return Dictionary::FrontendPipelineRunning->value;
    }
}
