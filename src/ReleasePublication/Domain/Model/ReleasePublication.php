<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\ReleasePublicationCreated;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusCreated;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\AbstractAggregateRoot;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

class ReleasePublication extends AbstractAggregateRoot implements ReleasePublicationInterface
{
    public function __construct(
        private readonly ReleasePublicationId $id,
        private readonly Name $branchName,
        private readonly StatusInterface $status,
        private readonly IssueList $readyToMergeTasks,
        private readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        Name $branchName,
        IssueList $readyToMergeTasks,
    ): self {
        $release = new self(
            id: ReleasePublicationId::generate($branchName),
            branchName: $branchName,
            status: new StatusCreated(),
            readyToMergeTasks: $readyToMergeTasks,
            createdAt: new \DateTimeImmutable(),
        );

        $release->raiseDomainEvent(new ReleasePublicationCreated(
            id: $release->id(),
            branchName: $release->branchName(),
            status: $release->status(),
            readyToMergeTasks: $readyToMergeTasks,
            createdAt: $release->createdAt(),
        ));

        return $release;
    }

    public function proceedToNextStatus(
        MergeRequestManagerInterface $mergeRequestManager,
        SourceCodeRepositoryInterface $frontendSourceCodeRepository,
        SourceCodeRepositoryInterface $backendSourceCodeRepository,
        ContinuousIntegrationClientInterface $frontendCiClient,
        ContinuousIntegrationClientInterface $backendCiClient,
        SetFrontendApplicationBranchNameCommitFactoryInterface $setFrontendApplicationBranchNameCommitFactory,
        TaskTrackerInterface $taskTracker,
    ): void {
        $this->status->proceedToNext(
            mergeRequestManager: $mergeRequestManager,
            frontendSourceCodeRepository: $frontendSourceCodeRepository,
            backendSourceCodeRepository: $backendSourceCodeRepository,
            frontendCiClient: $frontendCiClient,
            backendCiClient: $backendCiClient,
            setFrontendApplicationBranchNameCommitFactory: $setFrontendApplicationBranchNameCommitFactory,
            taskTracker: $taskTracker,
            context: $this,
        );
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
