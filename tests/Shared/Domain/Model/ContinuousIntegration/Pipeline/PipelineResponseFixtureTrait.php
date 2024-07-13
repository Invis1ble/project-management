<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\ContinuousIntegration\Pipeline;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;

trait PipelineResponseFixtureTrait
{
    public function pipelineResponseFixture(
        Pipeline\PipelineId $pipelineId,
        Project\ProjectId $projectId,
        Project\Name $projectName,
        Pipeline\Status $status,
        \DateTimeImmutable $createdAt,
    ): array {
        $pipeline = file_get_contents(__DIR__ . '/fixture/response/pipeline.200.json');
        $pipeline = json_decode($pipeline, true);

        return [
            'id' => $pipelineId->value(),
            'project_id' => $projectId->value(),
            'status' => $status->value,
            'created_at' => $createdAt->format(DATE_RFC3339_EXTENDED),
            'updated_at' => $createdAt->format(DATE_RFC3339_EXTENDED),
            'started_at' => $createdAt->format(DATE_RFC3339_EXTENDED),
            'finished_at' => $createdAt->format(DATE_RFC3339_EXTENDED),
            'committed_at' => $createdAt->format(DATE_RFC3339_EXTENDED),
            'web_url' => "http://127.0.0.1:3000/$projectName/-/pipelines/{$pipeline['id']}",
        ] + $pipeline;
    }
}
