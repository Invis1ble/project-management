<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model\Status;

use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

final readonly class StatusFrontendPipelineSuccess extends StatusFrontendPipelineFinished
{
    public function createBackendBranch(SourceCodeRepositoryInterface $repository, ReleasePublicationInterface $context): void
    {
        $repository->createBranch($context->branchName());

        $this->setReleaseStatus($context, new StatusBackendBranchCreated());
    }

    public function __toString(): string
    {
        return Dictionary::FrontendPipelineSuccess->value;
    }
}
