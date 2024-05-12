<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;

use Psr\Http\Message\UriInterface;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch;

final readonly class MergeRequest
{
    public function __construct(
        public MergeRequestId $id,
        public Name $name,
        public Project\ProjectId $projectId,
        public Project\Name $projectName,
        public Branch\Name $sourceBranchName,
        public Branch\Name $targetBranchName,
        public Status $status,
        public UriInterface $guiUrl,
        public ?Details $details,
    ) {
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

    public function withDetails(Details $details): self
    {
        return new self(
            $this->id,
            $this->name,
            $this->projectId,
            $this->projectName,
            $this->sourceBranchName,
            $this->targetBranchName,
            $this->status,
            $this->guiUrl,
            $details,
        );
    }
}
