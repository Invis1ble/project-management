<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Model;

use ReleaseManagement\Shared\Domain\Model\AggregateRootInterface;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegrationClientInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepositoryInterface;

interface ReleaseInterface extends AggregateRootInterface
{
    public function createFrontendBranch(SourceCodeRepositoryInterface $repository): void;

    public function createBackendBranch(SourceCodeRepositoryInterface $repository): void;

    public function awaitLatestFrontendPipeline(
        ContinuousIntegrationClientInterface $ciClient,
        \DateInterval $maxAwaitingTime = null,
    ): void;

    public function id(): ReleaseId;

    public function branchName(): ReleaseBranchName;

    public function status(): StatusInterface;

    public function createdAt(): \DateTimeImmutable;
}
