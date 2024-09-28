<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;

use Invis1ble\ProjectManagement\Shared\Domain\Model\AbstractList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;

/**
 * @extends AbstractList<Issue>
 */
final readonly class IssueList extends AbstractList
{
    /**
     * @var \SplObjectStorage<Key>
     */
    private \SplObjectStorage $storage;

    public function __construct(Issue ...$issues)
    {
        $this->storage = new class() extends \SplObjectStorage {
            public function getHash(object $object): string
            {
                if (!$object instanceof Key) {
                    throw new \InvalidArgumentException(sprintf(
                        'Object must be an instance of %s, %s given',
                        Key::class,
                        $object::class,
                    ));
                }

                return (string) $object;
            }
        };

        foreach ($issues as $issue) {
            $this->storage->attach($issue->key, $issue);
        }
    }

    public function mergeMergeRequests(MergeRequestManagerInterface $mergeRequestManager): self
    {
        return new self(
            ...(function (MergeRequestManagerInterface $mergeRequestManager): iterable {
                foreach ($this->elements() as $issue) {
                    yield $issue->mergeMergeRequests($mergeRequestManager);
                }
            })($mergeRequestManager),
        );
    }

    public function mergeRequestsToMerge(): MergeRequestList
    {
        $mergeRequests = new MergeRequestList();

        foreach ($this->elements() as $issue) {
            if (null !== $issue->mergeRequestsToMerge) {
                $mergeRequests = $mergeRequests->concat($issue->mergeRequestsToMerge);
            }
        }

        return $mergeRequests;
    }

    public function append(Issue $issue): self
    {
        return new self(
            ...(function (Issue $issue): iterable {
                yield from $this->elements();
                yield $issue;
            })($issue),
        );
    }

    public function replace(self $issues): self
    {
        $this->storage->removeAll($issues->storage);
        $this->storage->addAll($issues->storage);

        return new self(...$this->elements());
    }

    public function withStatus(Status $status): self
    {
        return new self(
            ...(function (Status $status): iterable {
                foreach ($this->elements() as $issue) {
                    yield $issue->withStatus($status);
                }
            })($status),
        );
    }

    public function onlyInStatus(Status $status): self
    {
        return $this->filter(fn (Issue $issue): bool => $issue->status->equals($status));
    }

    public function onlyWithoutMergeRequestsToMerge(): self
    {
        return $this->filter(fn (Issue $issue): bool => !$issue->containsMergeRequestToMerge());
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

    public function equals(AbstractList $other): bool
    {
        if (!$other instanceof self || $this->count() !== $other->count()) {
            return false;
        }

        foreach ($this->storage as $key) {
            if (!$other->storage->contains($key)) {
                return false;
            }

            if (!$this->storage[$key]->equals($other->storage[$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return iterable<Key>
     */
    public function toKeys(): iterable
    {
        return $this->pluck('key');
    }

    protected function elements(): iterable
    {
        $this->storage->rewind();

        while ($this->storage->valid()) {
            yield $this->storage->getInfo();

            $this->storage->next();
        }
    }

    protected function elementsEquals($element1, $element2): bool
    {
        return $element1::class === $element2::class
            && $element1->equals($element2);
    }
}
