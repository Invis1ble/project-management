<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\TaskTracker\Issue;

use ReleaseManagement\Shared\Domain\Model\AbstractList;

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
