<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\CommitId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Psr\Http\Message\UriInterface;

final readonly class Pipeline
{
    public function __construct(
        public ProjectId $projectId,
        public Ref $ref,
        public PipelineId $id,
        public CommitId $sha,
        public Status $status,
        public \DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt,
        public ?\DateTimeImmutable $startedAt,
        public ?\DateTimeImmutable $finishedAt,
        public ?\DateTimeImmutable $committedAt,
        public UriInterface $guiUrl,
    ) {
    }

    public function finished(): bool
    {
        return $this->status->finished();
    }

    public function inProgress(): bool
    {
        return $this->status->inProgress();
    }

    public function createdAfter(\DateTimeImmutable $datetime): bool
    {
        return $this->createdAt > $datetime;
    }
}
