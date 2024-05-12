<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\TaskTracker\Issue;

use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Board\BoardId;

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

    public function inActiveSprintOnBoard(BoardId $boardId): bool
    {
        return $this->sprints->containsActiveOnBoard($boardId);
    }

    public function __toString(): string
    {
        return (string) $this->key;
    }
}
