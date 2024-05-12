<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model\Status;

use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

final readonly class StatusCreated extends AbstractStatus
{
    public function createFrontendBranch(SourceCodeRepositoryInterface $repository, ReleasePublicationInterface $context): void
    {
        $repository->createBranch($context->branchName());

        $this->setReleaseStatus($context, new StatusFrontendBranchCreated());
    }

    public function __toString(): string
    {
        return Dictionary::Created->value;
    }
}
