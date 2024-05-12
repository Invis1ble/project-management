<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Infrastructure\Domain\Model\Entity;

use ReleaseManagement\ReleasePublication\Domain\Event\ReleasePublicationCreated;
use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use ReleaseManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusCreated;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusInterface;
use ReleaseManagement\Shared\Domain\Model\AbstractAggregateRoot;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

class ReleasePublication extends AbstractAggregateRoot implements ReleasePublicationInterface
{
    public function __construct(
        private ReleasePublicationId $id,
        private Name $branchName,
        private StatusInterface $status,
        private \DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        Name $branchName,
    ): self {
        $release = new self(
            ReleasePublicationId::generate($branchName),
            $branchName,
            new StatusCreated(),
            new \DateTimeImmutable(),
        );

        $release->raiseDomainEvent(new ReleasePublicationCreated(
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

    public function id(): ReleasePublicationId
    {
        return $this->id;
    }

    public function branchName(): Name
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
