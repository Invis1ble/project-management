<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model\Status;

use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

final readonly class StatusFrontendBranchCreated extends StatusFrontendPipelineAwaitable
{
    public function createFrontendBranch(SourceCodeRepositoryInterface $repository, ReleasePublicationInterface $context): void
    {
        // do nothing
    }

    public function __toString(): string
    {
        return Dictionary::FrontendBranchCreated->value;
    }
}
