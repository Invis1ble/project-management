<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model;

use ProjectManagement\HotfixPublication\Domain\Model\Status\StatusInterface;
use ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use ProjectManagement\Shared\Domain\Model\AggregateRootInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

interface HotfixPublicationInterface extends AggregateRootInterface
{
    public function proceedToNextStatus(
        MergeRequestManagerInterface $mergeRequestManager,
        SourceCodeRepositoryInterface $frontendSourceCodeRepository,
        SourceCodeRepositoryInterface $backendSourceCodeRepository,
        ContinuousIntegrationClientInterface $frontendCiClient,
        ContinuousIntegrationClientInterface $backendCiClient,
        SetFrontendApplicationBranchNameCommitFactoryInterface $setFrontendApplicationBranchNameCommitFactory,
        TaskTrackerInterface $taskTracker,
        ProjectResolverInterface $projectResolver,
    ): void;

    public function containsBackendMergeRequestToMerge(ProjectResolverInterface $projectResolver): bool;

    public function containsFrontendMergeRequestToMerge(ProjectResolverInterface $projectResolver): bool;

    public function id(): HotfixPublicationId;

    public function tagName(): Tag\VersionName;

    public function tagMessage(): Tag\Message;

    public function status(): StatusInterface;

    public function createdAt(): \DateTimeImmutable;

    public function hotfixes(): IssueList;
}
