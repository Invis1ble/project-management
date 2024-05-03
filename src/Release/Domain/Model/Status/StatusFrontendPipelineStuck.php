<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Model\Status;

final readonly class StatusFrontendPipelineStuck extends AbstractStatus
{
    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return Dictionary::FrontendPipelineStuck->value;
    }
}
