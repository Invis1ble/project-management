<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline;

use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectId;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Commit\CommitId;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Psr\Http\Message\UriFactoryInterface;

final readonly class PipelineFactory implements PipelineFactoryInterface
{
    public function __construct(private UriFactoryInterface $uriFactory)
    {
    }

    public function createPipeline(
        int $projectId,
        string $ref,
        int $id,
        string $sha,
        string $status,
        string $createdAt,
        ?string $updatedAt,
        ?string $startedAt,
        ?string $finishedAt,
        ?string $committedAt,
        string $guiUrl,
    ): Pipeline {
        return new Pipeline(
            projectId: ProjectId::from($projectId),
            ref: Ref::fromString($ref),
            id: PipelineId::from($id),
            sha: CommitId::fromString($sha),
            status: Status::from($status),
            createdAt: new \DateTimeImmutable($createdAt),
            updatedAt: null === $updatedAt ? null : new \DateTimeImmutable($updatedAt),
            startedAt: null === $startedAt ? null : new \DateTimeImmutable($startedAt),
            finishedAt: null === $finishedAt ? null : new \DateTimeImmutable($finishedAt),
            committedAt: null === $committedAt ? null : new \DateTimeImmutable($committedAt),
            guiUrl: $this->uriFactory->createUri($guiUrl),
        );
    }
}
