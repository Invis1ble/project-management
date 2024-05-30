<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;

use ProjectManagement\Shared\Domain\Model\AbstractList;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;

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

    public function containsBackendMergeRequestToMerge(ProjectResolverInterface $projectResolver): bool
    {
        return $this->exists(
            fn (Issue $issue): bool => $issue->containsBackendMergeRequestToMerge($projectResolver),
        );
    }

    public function containsFrontendMergeRequestToMerge(ProjectResolverInterface $projectResolver): bool
    {
        return $this->exists(
            fn (Issue $issue): bool => $issue->containsFrontendMergeRequestToMerge($projectResolver),
        );
    }

    /**
     * @return iterable<Key>
     */
    public function toKeys(): iterable
    {
        foreach ($this->elements as $element) {
            yield $element->key;
        }
    }

    protected function elements(): iterable
    {
        return $this->elements;
    }
}
