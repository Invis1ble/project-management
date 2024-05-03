<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Model\Status;

final readonly class StatusFrontendPipelineCanceled extends StatusFrontendPipelineFinished
{
    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return Dictionary::FrontendPipelineCanceled->value;
    }
}
