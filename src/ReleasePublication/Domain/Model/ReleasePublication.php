<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Domain\Model;

use ProjectManagement\ReleasePublication\Domain\Event\ReleasePublicationCreated;
use ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ProjectManagement\ReleasePublication\Domain\Model\Status\StatusCreated;
use ProjectManagement\ReleasePublication\Domain\Model\Status\StatusInterface;
use ProjectManagement\Shared\Domain\Model\AbstractAggregateRoot;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequestManagerInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

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
