<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Board\BoardId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint\SprintList;

final readonly class Issue implements \Stringable
{
    public function __construct(
        public IssueId $id,
        public Key $key,
        public TypeId $typeId,
        public Summary $summary,
        public SprintList $sprints,
        public ?MergeRequestList $mergeRequests,
        public ?MergeRequestList $mergeRequestsToMerge,
    ) {
    }

    public function mergeMergeRequests(MergeRequestManagerInterface $mergeRequestManager): self
    {
        return new self(
            $this->id,
            $this->key,
            $this->typeId,
            $this->summary,
            $this->sprints,
            $this->mergeRequests,
            $this->mergeRequestsToMerge?->mergeMergeRequests($mergeRequestManager),
        );
    }

    public function canonicalBranchName(): Name
    {
        return $this->key->toBranchName();
    }

    public function withMergeRequests(MergeRequestList $mergeRequests): self
    {
        return new self(
            $this->id,
            $this->key,
            $this->typeId,
            $this->summary,
            $this->sprints,
            $mergeRequests,
            $this->mergeRequestsToMerge,
        );
    }

    public function withMergeRequestsToMerge(MergeRequestList $mergeRequests): self
    {
        return new self(
            $this->id,
            $this->key,
            $this->typeId,
            $this->summary,
            $this->sprints,
            $this->mergeRequests,
            $mergeRequests,
        );
    }

    public function containsBackendMergeRequestToMerge(ProjectResolverInterface $projectResolver): bool
    {
        return $this->mergeRequestsToMerge->containsBackendMergeRequest($projectResolver);
    }

    public function containsFrontendMergeRequestToMerge(ProjectResolverInterface $projectResolver): bool
    {
        return $this->mergeRequestsToMerge->containsFrontendMergeRequest($projectResolver);
    }

    public function inActiveSprintOnBoard(BoardId $boardId): bool
    {
        return $this->sprints->containsActiveOnBoard($boardId);
    }

    public function __toString(): string
    {
        return (string) $this->key;
    }

    public function equals(self $other): bool
    {
        return $this->id->equals($other->id)
            && $this->key->equals($other->key)
            && $this->typeId->equals($other->typeId)
            && $this->summary->equals($other->summary)
            && $this->sprints->equals($other->sprints)
            && $this->mergeRequests?->equals($other->mergeRequests)
            && $this->mergeRequestsToMerge?->equals($other->mergeRequestsToMerge)
        ;
    }
}
