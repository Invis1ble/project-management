<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Model\Status;

use ReleaseManagement\Release\Domain\Model\ReleaseInterface;
use ReleaseManagement\Shared\Domain\Event\BranchCreated;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepositoryInterface;

final readonly class StatusFrontendPipelineSuccess extends StatusFrontendPipelineFinished
{
    /**
     * {@inheritdoc}
     */
    public function createBackendBranch(SourceCodeRepositoryInterface $repository, ReleaseInterface $context): void
    {
        $repository->createBranch($context->branchName());

        $this->setReleaseStatus($context, new StatusBackendBranchCreated());
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return Dictionary::FrontendPipelineSuccess->value;
    }
}
