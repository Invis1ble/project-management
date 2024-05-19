<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\TaskTracker\Issue;

use ReleaseManagement\Shared\Domain\Model\AbstractList;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequestManagerInterface;

/**
 * @extends AbstractList<Issue>
 */
final readonly class IssueList extends AbstractList
{
    private iterable $elements;

    public function __construct(Issue ...$issues)
    {
        $this->elements = $issues;
    }

    public function mergeMergeRequests(MergeRequestManagerInterface $mergeRequestManager): self
    {
        return new self(
            ...(function (MergeRequestManagerInterface $mergeRequestManager): iterable {
                foreach ($this->elements as $element) {
                    yield $element->mergeMergeRequests($mergeRequestManager);
                }
            })($mergeRequestManager),
        );
    }

    public function mergeRequestsToMerge(): MergeRequestList
    {
        $mergeRequests = new MergeRequestList();

        foreach ($this->elements as $element) {
            $mergeRequests = $mergeRequests->concat($element->mergeRequestsToMerge);
        }

        return $mergeRequests;
    }

    public function append(Issue $issue): self
    {
        return new self(
            ...(function (Issue $issue): iterable {
                yield from $this->elements;
                yield $issue;
            })($issue),
        );
    }

    protected function elements(): iterable
    {
        return $this->elements;
    }
}
