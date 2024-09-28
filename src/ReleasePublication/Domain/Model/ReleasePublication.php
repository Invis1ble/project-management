<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\ReleasePublicationCreated;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\ReleasePublicationTagSet;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusCreated;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\AbstractAggregateRoot;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\UpdateExtraDeploymentBranchMergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\StatusProviderInterface;
use Psr\Clock\ClockInterface;

class ReleasePublication extends AbstractAggregateRoot implements ReleasePublicationInterface
{
    public function __construct(
        private ReleasePublicationId $id,
        private Name $branchName,
        private StatusInterface $status,
        private ?Tag\VersionName $tagName,
        private ?Tag\Message $tagMessage,
        private IssueList $tasks,
        private \DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        Name $branchName,
        IssueList $tasks,
        ClockInterface $clock,
    ): self {
        $publication = new self(
            id: ReleasePublicationId::fromBranchName($branchName),
            branchName: $branchName,
            status: new StatusCreated(),
            tagName: null,
            tagMessage: null,
            tasks: $tasks,
            createdAt: $clock->now(),
        );

        $publication->raiseDomainEvent(new ReleasePublicationCreated(
            id: $publication->id,
            branchName: $publication->branchName,
            tagName: null,
            tagMessage: null,
            status: $publication->status,
            tasks: $tasks,
            createdAt: $publication->createdAt,
        ));

        return $publication;
    }

    public function publish(
        Tag\VersionName $tagName,
        Tag\Message $tagMessage,
        MergeRequestManagerInterface $mergeRequestManager,
        SourceCodeRepositoryInterface $frontendSourceCodeRepository,
        SourceCodeRepositoryInterface $backendSourceCodeRepository,
        ContinuousIntegrationClientInterface $frontendCiClient,
        ContinuousIntegrationClientInterface $backendCiClient,
        SetFrontendApplicationBranchNameCommitFactoryInterface $setFrontendApplicationBranchNameCommitFactory,
        UpdateExtraDeploymentBranchMergeRequestFactoryInterface $updateExtraDeploymentBranchMergeRequestFactory,
        TaskTrackerInterface $taskTracker,
        StatusProviderInterface $issueStatusProvider,
        \DateInterval $pipelineMaxAwaitingTime,
        \DateInterval $pipelineTickInterval,
    ): void {
        $this->tagName = $tagName;
        $this->tagMessage = $tagMessage;

        $this->raiseDomainEvent(new ReleasePublicationTagSet(
            id: $this->id,
            branchName: $this->branchName,
            tagName: $this->tagName,
            tagMessage: $this->tagMessage,
            status: $this->status,
            tasks: $this->tasks,
            createdAt: $this->createdAt,
        ));

        $this->status->proceedToNext(
            mergeRequestManager: $mergeRequestManager,
            frontendSourceCodeRepository: $frontendSourceCodeRepository,
            backendSourceCodeRepository: $backendSourceCodeRepository,
            frontendCiClient: $frontendCiClient,
            backendCiClient: $backendCiClient,
            setFrontendApplicationBranchNameCommitFactory: $setFrontendApplicationBranchNameCommitFactory,
            updateExtraDeploymentBranchMergeRequestFactory: $updateExtraDeploymentBranchMergeRequestFactory,
            taskTracker: $taskTracker,
            issueStatusProvider: $issueStatusProvider,
            pipelineTickInterval: $pipelineTickInterval,
            context: $this,
            pipelineMaxAwaitingTime: $pipelineMaxAwaitingTime,
        );
    }

    public function proceedToNextStatus(
        MergeRequestManagerInterface $mergeRequestManager,
        SourceCodeRepositoryInterface $frontendSourceCodeRepository,
        SourceCodeRepositoryInterface $backendSourceCodeRepository,
        ContinuousIntegrationClientInterface $frontendCiClient,
        ContinuousIntegrationClientInterface $backendCiClient,
        SetFrontendApplicationBranchNameCommitFactoryInterface $setFrontendApplicationBranchNameCommitFactory,
        UpdateExtraDeploymentBranchMergeRequestFactoryInterface $updateExtraDeploymentBranchMergeRequestFactory,
        TaskTrackerInterface $taskTracker,
        StatusProviderInterface $issueStatusProvider,
        \DateInterval $pipelineMaxAwaitingTime,
        \DateInterval $pipelineTickInterval,
    ): void {
        $this->status->proceedToNext(
            mergeRequestManager: $mergeRequestManager,
            frontendSourceCodeRepository: $frontendSourceCodeRepository,
            backendSourceCodeRepository: $backendSourceCodeRepository,
            frontendCiClient: $frontendCiClient,
            backendCiClient: $backendCiClient,
            setFrontendApplicationBranchNameCommitFactory: $setFrontendApplicationBranchNameCommitFactory,
            updateExtraDeploymentBranchMergeRequestFactory: $updateExtraDeploymentBranchMergeRequestFactory,
            taskTracker: $taskTracker,
            issueStatusProvider: $issueStatusProvider,
            pipelineTickInterval: $pipelineTickInterval,
            context: $this,
            pipelineMaxAwaitingTime: $pipelineMaxAwaitingTime,
        );
    }

    public function prepared(): bool
    {
        return $this->status->prepared();
    }

    public function published(): bool
    {
        return $this->status->published();
    }

    public function id(): ReleasePublicationId
    {
        return $this->id;
    }

    public function branchName(): Name
    {
        return $this->branchName;
    }

    public function tagName(): ?Tag\VersionName
    {
        return $this->tagName;
    }

    public function tagMessage(): ?Tag\Message
    {
        return $this->tagMessage;
    }

    public function status(): StatusInterface
    {
        return $this->status;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function tasks(): IssueList
    {
        return $this->tasks;
    }
}
