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
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\UpdateExtraDeployBranchMergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use Psr\Clock\ClockInterface;

class ReleasePublication extends AbstractAggregateRoot implements ReleasePublicationInterface
{
    public function __construct(
        private readonly ReleasePublicationId $id,
        private readonly Name $branchName,
        private StatusInterface $status,
        private ?Tag\VersionName $tagName,
        private ?Tag\Message $tagMessage,
        private IssueList $readyToMergeTasks,
        private readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        Name $branchName,
        IssueList $readyToMergeTasks,
        ClockInterface $clock,
    ): self {
        $release = new self(
            id: ReleasePublicationId::fromBranchName($branchName),
            branchName: $branchName,
            status: new StatusCreated(),
            tagName: null,
            tagMessage: null,
            readyToMergeTasks: $readyToMergeTasks,
            createdAt: $clock->now(),
        );

        $release->raiseDomainEvent(new ReleasePublicationCreated(
            id: $release->id,
            branchName: $release->branchName,
            tagName: null,
            tagMessage: null,
            status: $release->status,
            readyToMergeTasks: $readyToMergeTasks,
            createdAt: $release->createdAt,
        ));

        return $release;
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
        UpdateExtraDeployBranchMergeRequestFactoryInterface $updateExtraDeployBranchMergeRequestFactory,
        TaskTrackerInterface $taskTracker,
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
            readyToMergeTasks: $this->readyToMergeTasks,
            createdAt: $this->createdAt,
        ));

        $this->status->proceedToNext(
            mergeRequestManager: $mergeRequestManager,
            frontendSourceCodeRepository: $frontendSourceCodeRepository,
            backendSourceCodeRepository: $backendSourceCodeRepository,
            frontendCiClient: $frontendCiClient,
            backendCiClient: $backendCiClient,
            setFrontendApplicationBranchNameCommitFactory: $setFrontendApplicationBranchNameCommitFactory,
            updateExtraDeployBranchMergeRequestFactory: $updateExtraDeployBranchMergeRequestFactory,
            taskTracker: $taskTracker,
            pipelineMaxAwaitingTime: $pipelineMaxAwaitingTime,
            pipelineTickInterval: $pipelineTickInterval,
            context: $this,
        );
    }

    public function proceedToNextStatus(
        MergeRequestManagerInterface $mergeRequestManager,
        SourceCodeRepositoryInterface $frontendSourceCodeRepository,
        SourceCodeRepositoryInterface $backendSourceCodeRepository,
        ContinuousIntegrationClientInterface $frontendCiClient,
        ContinuousIntegrationClientInterface $backendCiClient,
        SetFrontendApplicationBranchNameCommitFactoryInterface $setFrontendApplicationBranchNameCommitFactory,
        UpdateExtraDeployBranchMergeRequestFactoryInterface $updateExtraDeployBranchMergeRequestFactory,
        TaskTrackerInterface $taskTracker,
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
            updateExtraDeployBranchMergeRequestFactory: $updateExtraDeployBranchMergeRequestFactory,
            taskTracker: $taskTracker,
            pipelineMaxAwaitingTime: $pipelineMaxAwaitingTime,
            pipelineTickInterval: $pipelineTickInterval,
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

    public function readyToMergeTasks(): IssueList
    {
        return $this->readyToMergeTasks;
    }
}
