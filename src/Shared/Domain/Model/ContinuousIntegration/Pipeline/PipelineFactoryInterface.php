<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline;

interface PipelineFactoryInterface
{
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
    ): Pipeline;
}
