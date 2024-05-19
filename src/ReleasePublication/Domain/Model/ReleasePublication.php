<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model;

use ReleaseManagement\ReleasePublication\Domain\Event\ReleasePublicationCreated;
use ReleaseManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusCreated;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusInterface;
use ReleaseManagement\Shared\Domain\Model\AbstractAggregateRoot;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequestManagerInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

class ReleasePublication extends AbstractAggregateRoot implements ReleasePublicationInterface
{
    public function __construct(
        private readonly ReleasePublicationId $id,
        private readonly Name $branchName,
        private StatusInterface $status,
        private readonly IssueList $readyToMergeTasks,
        private readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        Name $branchName,
        IssueList $tasks,
    ): self {
        $release = new self(
            ReleasePublicationId::generate($branchName),
            $branchName,
            new StatusCreated(),
            $tasks,
            new \DateTimeImmutable(),
        );

        $release->raiseDomainEvent(new ReleasePublicationCreated(
            id: $release->id(),
            branchName: $release->branchName(),
            status: $release->status(),
            readyToMergeTasks: $tasks,
            createdAt: $release->createdAt(),
        ));

        return $release;
    }

    public function proceedToNextStatus(
        MergeRequestManagerInterface $mergeRequestManager,
    ): void {
        $this->status->proceedToNext($mergeRequestManager, $this);
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

    public function readyToMergeTasks(): IssueList
    {
        return $this->readyToMergeTasks;
    }
}
