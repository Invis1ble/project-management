<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;

interface JobFactoryInterface
{
    public function createJob(
        int $id,
        int $projectId,
        ?int $pipelineId,
        ?string $sha,
        string $name,
        string $ref,
        string $status,
        ?string $guiUrl,
        string $createdAt,
        ?string $startedAt,
        ?string $finishedAt,
        ?string $pipelineStatus,
        ?string $pipelineCreatedAt,
        ?string $pipelineUpdatedAt,
        ?string $pipelineStartedAt,
        ?string $pipelineFinishedAt,
        ?string $pipelineCommittedAt,
        ?string $pipelineGuiUrl,
    ): Job;
}
