<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model;

use ProjectManagement\HotfixPublication\Domain\Event\HotfixPublicationCreated;
use ProjectManagement\HotfixPublication\Domain\Model\Status\StatusCreated;
use ProjectManagement\HotfixPublication\Domain\Model\Status\StatusInterface;
use ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use ProjectManagement\Shared\Domain\Model\AbstractAggregateRoot;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequestManagerInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

class HotfixPublication extends AbstractAggregateRoot implements HotfixPublicationInterface
{
    public function __construct(
        private readonly HotfixPublicationId $id,
        private readonly StatusInterface $status,
        private readonly IssueList $hotfixes,
        private readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(IssueList $hotfixes): self
    {
        if ($hotfixes->empty()) {
            throw new \InvalidArgumentException('No hotfixes provided');
        }

        $hotfix = new self(
            id: HotfixPublicationId::generate($hotfixes),
            status: new StatusCreated(),
            hotfixes: $hotfixes,
            createdAt: new \DateTimeImmutable(),
        );

        $hotfix->raiseDomainEvent(new HotfixPublicationCreated(
            id: $hotfix->id(),
            status: $hotfix->status(),
            hotfixes: $hotfixes,
            createdAt: $hotfix->createdAt(),
        ));

        return $hotfix;
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

    public function id(): HotfixPublicationId
    {
        return $this->id;
    }

    public function status(): StatusInterface
    {
        return $this->status;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function readyToMergeHotfixes(): IssueList
    {
        return $this->hotfixes;
    }
}
