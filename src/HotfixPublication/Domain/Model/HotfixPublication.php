<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Domain\Model;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Event\HotfixPublicationCreated;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusCreated;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\AbstractAggregateRoot;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\UpdateExtraDeploymentBranchMergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\StatusProviderInterface;
use Psr\Clock\ClockInterface;

class HotfixPublication extends AbstractAggregateRoot implements HotfixPublicationInterface
{
    public function __construct(
        private HotfixPublicationId $id,
        private Tag\VersionName $tagName,
        private Tag\Message $tagMessage,
        private StatusInterface $status,
        private IssueList $hotfixes,
        private \DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        Tag\VersionName $tagName,
        Tag\Message $tagMessage,
        IssueList $hotfixes,
        ClockInterface $clock,
    ): self {
        if ($hotfixes->empty()) {
            throw new \InvalidArgumentException('No hotfixes provided');
        }

        $publication = new self(
            id: HotfixPublicationId::fromVersionName($tagName),
            tagName: $tagName,
            tagMessage: $tagMessage,
            status: new StatusCreated(),
            hotfixes: $hotfixes,
            createdAt: $clock->now(),
        );

        $publication->raiseDomainEvent(new HotfixPublicationCreated(
            id: $publication->id(),
            status: $publication->status(),
            hotfixes: $hotfixes,
            createdAt: $publication->createdAt(),
        ));

        return $publication;
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
        ProjectResolverInterface $projectResolver,
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
            projectResolver: $projectResolver,
            issueStatusProvider: $issueStatusProvider,
            pipelineMaxAwaitingTime: $pipelineMaxAwaitingTime,
            pipelineTickInterval: $pipelineTickInterval,
            publication: $this,
        );
    }

    public function containsBackendMergeRequestToMerge(ProjectResolverInterface $projectResolver): bool
    {
        return $this->hotfixes->containsBackendMergeRequestToMerge($projectResolver);
    }

    public function containsFrontendMergeRequestToMerge(ProjectResolverInterface $projectResolver): bool
    {
        return $this->hotfixes->containsFrontendMergeRequestToMerge($projectResolver);
    }

    public function published(): bool
    {
        return $this->status->published();
    }

    public function id(): HotfixPublicationId
    {
        return $this->id;
    }

    public function tagName(): Tag\VersionName
    {
        return $this->tagName;
    }

    public function tagMessage(): Tag\Message
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

    public function hotfixes(): IssueList
    {
        return $this->hotfixes;
    }
}
