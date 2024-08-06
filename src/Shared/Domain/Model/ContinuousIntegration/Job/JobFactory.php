<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job\Status\StatusFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\PipelineFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;

final readonly class JobFactory implements JobFactoryInterface
{
    public function __construct(
        private StatusFactoryInterface $statusFactory,
        private PipelineFactoryInterface $pipelineFactory,
    ) {
    }

    public function createJob(
        int $id,
        int $projectId,
        ?int $pipelineId,
        ?string $sha,
        string $name,
        string $ref,
        string $status,
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
    ): Job {
        if (null === $pipelineId) {
            $pipeline = null;
        } else {
            $pipeline = $this->pipelineFactory->createPipeline(
                projectId: $projectId,
                ref: $ref,
                id: $pipelineId,
                sha: $sha,
                status: $pipelineStatus,
                createdAt: $pipelineCreatedAt,
                updatedAt: $pipelineUpdatedAt,
                startedAt: $pipelineStartedAt,
                finishedAt: $pipelineFinishedAt,
                committedAt: $pipelineCommittedAt,
                guiUrl: $pipelineGuiUrl,
            );
        }

        return new Job(
            id: JobId::from($id),
            name: Name::fromString($name),
            ref: Ref::fromString($ref),
            status: $this->statusFactory->createStatus(Status\Dictionary::from($status)),
            pipeline: $pipeline,
            createdAt: new \DateTimeImmutable($createdAt),
            startedAt: null === $startedAt ? null : new \DateTimeImmutable($startedAt),
            finishedAt: null === $finishedAt ? null : new \DateTimeImmutable($finishedAt),
        );
    }
}
