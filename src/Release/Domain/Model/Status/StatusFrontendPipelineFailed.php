<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Model\Status;

final readonly class StatusFrontendPipelineFailed extends StatusFrontendPipelineFinished
{
    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return Dictionary::FrontendPipelineFailed->value;
    }
}
