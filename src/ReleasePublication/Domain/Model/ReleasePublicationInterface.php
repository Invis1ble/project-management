<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model;

use ReleaseManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusInterface;
use ReleaseManagement\Shared\Domain\Model\AggregateRootInterface;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

interface ReleasePublicationInterface extends AggregateRootInterface
{
    public function createFrontendBranch(SourceCodeRepositoryInterface $repository): void;

    public function createBackendBranch(SourceCodeRepositoryInterface $repository): void;

    public function awaitLatestFrontendPipeline(
        ContinuousIntegrationClientInterface $ciClient,
        \DateInterval $maxAwaitingTime = null,
    ): void;

    public function id(): ReleasePublicationId;

    public function branchName(): Name;

    public function status(): StatusInterface;

    public function createdAt(): \DateTimeImmutable;
}
