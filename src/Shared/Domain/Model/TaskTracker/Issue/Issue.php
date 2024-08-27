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
        public bool $subtask,
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
            $this->subtask,
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
            $this->subtask,
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
            $this->subtask,
            $this->summary,
            $this->sprints,
            $this->mergeRequests,
            $mergeRequests,
        );
    }

    public function containsMergeRequestToMerge(): bool
    {
        return null !== $this->mergeRequestsToMerge
            && !$this->mergeRequestsToMerge->empty();
    }

    public function containsBackendMergeRequestToMerge(ProjectResolverInterface $projectResolver): bool
    {
        return null !== $this->mergeRequestsToMerge
            && $this->mergeRequestsToMerge->containsBackendMergeRequest($projectResolver);
    }

    public function containsFrontendMergeRequestToMerge(ProjectResolverInterface $projectResolver): bool
    {
        return null !== $this->mergeRequestsToMerge
            && $this->mergeRequestsToMerge->containsFrontendMergeRequest($projectResolver);
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
        if (null === $this->mergeRequests) {
            if (null !== $other->mergeRequests) {
                return false;
            }
        } elseif (null === $other->mergeRequests) {
            return false;
        } elseif (!$this->mergeRequests->equals($other->mergeRequests)) {
            return false;
        }

        if (null === $this->mergeRequestsToMerge) {
            if (null !== $other->mergeRequestsToMerge) {
                return false;
            }
        } elseif (null === $other->mergeRequestsToMerge) {
            return false;
        } elseif (!$this->mergeRequestsToMerge->equals($other->mergeRequestsToMerge)) {
            return false;
        }

        return $this->id->equals($other->id)
            && $this->key->equals($other->key)
            && $this->typeId->equals($other->typeId)
            && $this->summary->equals($other->summary)
            && $this->sprints->equals($other->sprints)
        ;
    }
}
