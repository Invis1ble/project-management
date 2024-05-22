<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Domain\Model;

use ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ProjectManagement\ReleasePublication\Domain\Model\Status\StatusInterface;
use ProjectManagement\Shared\Domain\Model\AggregateRootInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequestManagerInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

interface ReleasePublicationInterface extends AggregateRootInterface
{
    public function proceedToNextStatus(
        MergeRequestManagerInterface $mergeRequestManager,
    ): void;


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

    public function readyToMergeTasks(): IssueList;
}
