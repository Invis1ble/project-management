<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model\Status;

use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

final readonly class StatusBackendBranchCreated extends AbstractStatus
{
    public function createBackendBranch(SourceCodeRepositoryInterface $repository, ReleasePublicationInterface $context): void
    {
        // do nothing
    }

    public function __toString(): string
    {
        return Dictionary::BackendBranchCreated->value;
    }
}
