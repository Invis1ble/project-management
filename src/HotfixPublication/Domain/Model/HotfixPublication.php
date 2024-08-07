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
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\UpdateExtraDeployBranchMergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
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

        $hotfix = new self(
            id: HotfixPublicationId::fromVersionName($tagName),
            tagName: $tagName,
            tagMessage: $tagMessage,
            status: new StatusCreated(),
            hotfixes: $hotfixes,
            createdAt: $clock->now(),
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
        UpdateExtraDeployBranchMergeRequestFactoryInterface $updateExtraDeployBranchMergeRequestFactory,
        TaskTrackerInterface $taskTracker,
        ProjectResolverInterface $projectResolver,
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
            projectResolver: $projectResolver,
            pipelineMaxAwaitingTime: $pipelineMaxAwaitingTime,
            pipelineTickInterval: $pipelineTickInterval,
            context: $this,
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
