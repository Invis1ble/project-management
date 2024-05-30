<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;

use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;
use Psr\Http\Message\UriInterface;

final readonly class MergeRequest
{
    public function __construct(
        public MergeRequestId $id,
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
            throw new \RuntimeException("Merge request $this->id details not set");
        }

        return $this->details->merge($mergeRequestManager, $this);
    }

    public function createCopyWithNewTargetBranch(
        MergeRequestManagerInterface $mergeRequestManager,
        Branch\Name $targetBranchName,
        Branch\Name $newTargetBranchName,
    ): ?self {
        if (!$this->targetBranchName->equals($targetBranchName)) {
            return null;
        }

        if (null === $this->details) {
            throw new \RuntimeException("Merge request $this->id details not set");
        }

        return $mergeRequestManager->createMergeRequest(
            projectId: $this->projectId,
            title: $this->title,
            sourceBranchName: $this->sourceBranchName,
            targetBranchName: $newTargetBranchName,
        );
    }

    public function withDetails(Details $details): self
    {
        return new self(
            $this->id,
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
}
