<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Model\Status;

use ReleaseManagement\Release\Domain\Model\ReleaseInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepositoryInterface;

final readonly class StatusCreated extends AbstractStatus
{
    /**
     * {@inheritdoc}
     */
    public function createFrontendBranch(SourceCodeRepositoryInterface $repository, ReleaseInterface $context): void
    {
        $repository->createBranch($context->branchName());

        $this->setReleaseStatus($context, new StatusFrontendBranchCreated());
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return Dictionary::Created->value;
    }
}
