<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;

use Psr\Http\Message\UriInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequestManagerInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch;

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

    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'name' => (string) $this->name,
            'project_id' => $this->projectId->value(),
            'project_name' => (string) $this->projectName,
            'source_branch_name' => (string) $this->sourceBranchName,
            'target_branch_name' => (string) $this->targetBranchName,
            'status' => $this->status->value,
            'gui_url' => (string) $this->guiUrl,
        ];
    }
}
