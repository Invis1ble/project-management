<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\AggregateRootInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\UpdateExtraDeploymentBranchMergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

interface HotfixPublicationInterface extends AggregateRootInterface
{
    public function proceedToNextStatus(
        MergeRequestManagerInterface $mergeRequestManager,
        SourceCodeRepositoryInterface $frontendSourceCodeRepository,
        SourceCodeRepositoryInterface $backendSourceCodeRepository,
        ContinuousIntegrationClientInterface $frontendCiClient,
        ContinuousIntegrationClientInterface $backendCiClient,
        SetFrontendApplicationBranchNameCommitFactoryInterface $setFrontendApplicationBranchNameCommitFactory,
        UpdateExtraDeploymentBranchMergeRequestFactoryInterface $updateExtraDeploymentBranchMergeRequestFactory,
        TaskTrackerInterface $taskTracker,
        ProjectResolverInterface $projectResolver,
        \DateInterval $pipelineMaxAwaitingTime,
        \DateInterval $pipelineTickInterval,
    ): void;

    public function containsBackendMergeRequestToMerge(ProjectResolverInterface $projectResolver): bool;

    public function containsFrontendMergeRequestToMerge(ProjectResolverInterface $projectResolver): bool;

    public function published(): bool;

    public function id(): HotfixPublicationId;

    public function tagName(): Tag\VersionName;

    public function tagMessage(): Tag\Message;

    public function status(): StatusInterface;

    public function createdAt(): \DateTimeImmutable;

    public function hotfixes(): IssueList;
}
