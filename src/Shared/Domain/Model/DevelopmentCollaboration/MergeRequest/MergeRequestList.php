<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;

use ReleaseManagement\Shared\Domain\Model\AbstractList;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequestManagerInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;

/**
 * @extends AbstractList<MergeRequest>
 */
final readonly class MergeRequestList extends AbstractList
{
    private iterable $elements;

    public function __construct(MergeRequest ...$mergeRequests)
    {
        $this->elements = $mergeRequests;
    }

    public function mergeMergeRequests(MergeRequestManagerInterface $mergeRequestManager): self
    {
        return new self(
            ...(function (MergeRequestManagerInterface $mergeRequestManager): iterable {
                foreach ($this->elements as $element) {
                    yield $element->merge($mergeRequestManager);
                }
            })($mergeRequestManager),
        );
    }

    public function append(MergeRequest $mergeRequest): self
    {
        return new self(
            ...(function (MergeRequest $mergeRequest): iterable {
                yield from $this->elements;
                yield $mergeRequest;
            })($mergeRequest),
        );
    }

    public function concat(self $list): self
    {
        return new self(
            ...$this->elements,
            ...$list->elements,
        );
    }

    public function targetToBranch(Name $branchName): self
    {
        return $this->filter(fn (MergeRequest $mr): bool => $mr->targetBranchName->equals($branchName));
    }

    public function awaitingToMerge(): self
    {
        return $this->filter(fn (MergeRequest $mr): bool => $mr->open());
    }

    public function relevantToSourceBranch(Name $branchName): self
    {
        return $this->filter(fn (MergeRequest $mr): bool => $mr->sourceRelevant($branchName));
    }

    protected function elements(): iterable
    {
        return $this->elements;
    }
}
