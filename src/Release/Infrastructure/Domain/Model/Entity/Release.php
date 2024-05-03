<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Infrastructure\Domain\Model\Entity;

use ReleaseManagement\Release\Domain\Event\ReleaseCreated;
use ReleaseManagement\Release\Domain\Model\ReleaseBranchName;
use ReleaseManagement\Release\Domain\Model\ReleaseId;
use ReleaseManagement\Release\Domain\Model\ReleaseInterface;
use ReleaseManagement\Release\Domain\Model\Status\StatusCreated;
use ReleaseManagement\Release\Domain\Model\StatusInterface;
use ReleaseManagement\Shared\Domain\Model\AbstractAggregateRoot;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegrationClientInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepositoryInterface;

class Release extends AbstractAggregateRoot implements ReleaseInterface
{
    public function __construct(
        private ReleaseId $id,
        private ReleaseBranchName $branchName,
        private StatusInterface $status,
        private \DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        ReleaseBranchName $branchName,
    ): self {
        $release = new self(
            ReleaseId::generate($branchName),
            $branchName,
            new StatusCreated(),
            new \DateTimeImmutable(),
        );

        $release->raiseDomainEvent(new ReleaseCreated(
            id: $release->id(),
            branchName: $release->branchName(),
            status: $release->status(),
            createdAt: $release->createdAt(),
        ));

        return $release;
    }

    public function createFrontendBranch(SourceCodeRepositoryInterface $repository): void
    {
        $this->status->createFrontendBranch($repository, $this);
    }

    public function createBackendBranch(SourceCodeRepositoryInterface $repository): void
    {
        $this->status->createBackendBranch($repository, $this);
    }

    public function awaitLatestFrontendPipeline(
        ContinuousIntegrationClientInterface $ciClient,
        \DateInterval $maxAwaitingTime = null,
    ): void {
        $this->status->awaitLatestFrontendPipeline($ciClient, $this, $maxAwaitingTime);
    }

    public function id(): ReleaseId
    {
        return $this->id;
    }

    public function branchName(): ReleaseBranchName
    {
        return $this->branchName;
    }

    public function status(): StatusInterface
    {
        return $this->status;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
