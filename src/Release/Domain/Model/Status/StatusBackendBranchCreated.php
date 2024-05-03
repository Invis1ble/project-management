<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Model\Status;

use ReleaseManagement\Release\Domain\Model\ReleaseInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepositoryInterface;

final readonly class StatusBackendBranchCreated extends AbstractStatus
{
    /**
     * {@inheritdoc}
     */
    public function createBackendBranch(SourceCodeRepositoryInterface $repository, ReleaseInterface $context): void
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return Dictionary::BackendBranchCreated->value;
    }
}
