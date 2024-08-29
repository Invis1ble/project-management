<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Psr\Http\Message\UriInterface;

final readonly class MergeRequest implements \Stringable
{
    public function __construct(
        public MergeRequestIid $iid,
        public Title $title,
        public Project\ProjectId $projectId,
        public Project\Name $projectName,
        public Branch\Name $sourceBranchName,
        public Branch\Name $targetBranchName,
        public Status $status,
        public UriInterface $guiUrl,
        public ?Details $details,
    ) {
    }

    public function merge(MergeRequestManagerInterface $mergeRequestManager): self
    {
        if ($this->merged()) {
            return $this;
        }

        if (null === $this->details) {
            throw new \RuntimeException("Merge request $this->iid details not set");
        }

        return $this->details->merge($mergeRequestManager, $this);
    }

    public function withDetails(Details $details): self
    {
        return new self(
            $this->iid,
            $this->title,
            $this->projectId,
            $this->projectName,
            $this->sourceBranchName,
            $this->targetBranchName,
            $this->status,
            $this->guiUrl,
            $details,
        );
    }

    public function backend(ProjectResolverInterface $projectResolver): bool
    {
        return $projectResolver->backend($this->projectId);
    }

    public function frontend(ProjectResolverInterface $projectResolver): bool
    {
        return $projectResolver->frontend($this->projectId);
    }

    public function open(): bool
    {
        return $this->status->open();
    }

    public function merged(): bool
    {
        return $this->status->merged();
    }

    public function declined(): bool
    {
        return $this->status->declined();
    }

    public function sourceEquals(Branch\Name $branchName): bool
    {
        return $this->sourceBranchName->equals($branchName);
    }

    public function sourceRelevant(Branch\Name $branchName): bool
    {
        return $this->sourceBranchName->relevant($branchName);
    }

    public function mayBeMergeable(): bool
    {
        if (null === $this->details) {
            throw new \RuntimeException("Merge request $this details not set");
        }

        return $this->details->mayBeMergeable();
    }

    public function mergeable(): bool
    {
        if (null === $this->details) {
            throw new \RuntimeException("Merge request $this details not set");
        }

        return $this->details->mergeable();
    }

    public function targetToBranch(Branch\Name $branchName): bool
    {
        return $this->targetBranchName->equals($branchName);
    }

    public function equals(self $other): bool
    {
        if (null === $this->details) {
            if (null !== $other->details) {
                return false;
            }
        } elseif (null === $other->details) {
            return false;
        } elseif (!$this->details->equals($other->details)) {
            return false;
        }

        return $this->iid->equals($other->iid)
            && $this->title->equals($other->title)
            && $this->projectId->equals($other->projectId)
            && $this->projectName->equals($other->projectName)
            && $this->sourceBranchName->equals($other->sourceBranchName)
            && $this->targetBranchName->equals($other->targetBranchName)
            && $this->status->equals($other->status)
            && (string) $this->guiUrl === (string) $other->guiUrl
        ;
    }

    public function __toString(): string
    {
        return "$this->projectName!$this->iid";
    }
}
