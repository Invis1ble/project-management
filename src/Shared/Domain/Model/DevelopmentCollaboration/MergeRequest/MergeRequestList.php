<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;

use Invis1ble\ProjectManagement\Shared\Domain\Model\AbstractList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;

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
            ...(function () use ($mergeRequestManager): iterable {
                foreach ($this->elements as $mr) {
                    yield $mr->merge($mergeRequestManager);
                }
            })(),
        );
    }

    public function createCopiesWithNewTargetBranch(
        MergeRequestManagerInterface $mergeRequestManager,
        Branch\Name $targetBranchName,
        Branch\Name $newTargetBranchName,
    ): self {
        return new self(
            ...(function () use ($mergeRequestManager, $targetBranchName, $newTargetBranchName): iterable {
                foreach ($this->elements as $mr) {
                    $mr = $mr->createCopyWithNewTargetBranch($mergeRequestManager, $targetBranchName, $newTargetBranchName);

                    if (null !== $mr) {
                        yield $mr;
                    }
                }
            })(),
        );
    }

    public function append(MergeRequest $mergeRequest): self
    {
        return new self(
            ...(function () use ($mergeRequest): iterable {
                yield from $this->elements;
                yield $mergeRequest;
            })(),
        );
    }

    public function concat(self $list): self
    {
        return new self(
            ...$this->elements,
            ...$list->elements,
        );
    }

    public function targetToBranch(Branch\Name $branchName): self
    {
        return $this->filter(fn (MergeRequest $mr): bool => $mr->targetBranchName->equals($branchName));
    }

    public function awaitingToMerge(): self
    {
        return $this->filter(fn (MergeRequest $mr): bool => $mr->open());
    }

    public function relevantToSourceBranch(Branch\Name $branchName): self
    {
        return $this->filter(fn (MergeRequest $mr): bool => $mr->sourceRelevant($branchName));
    }

    public function containsBackendMergeRequest(ProjectResolverInterface $projectResolver): bool
    {
        return $this->exists(fn (MergeRequest $mr): bool => $mr->backend($projectResolver));
    }

    public function containsFrontendMergeRequest(ProjectResolverInterface $projectResolver): bool
    {
        return $this->exists(fn (MergeRequest $mr): bool => $mr->frontend($projectResolver));
    }

    protected function elements(): iterable
    {
        return $this->elements;
    }

    protected function elementsEquals($element1, $element2): bool
    {
        return $element1::class === $element2::class
            && $element1->equals($element2);
    }
}
