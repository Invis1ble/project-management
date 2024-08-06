<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\ContinuousIntegration\Job;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;

trait JobResponseFixtureTrait
{
    public function jobResponseFixture(
        Job\JobId $jobId,
        Job\Name $jobName,
        Ref $ref,
        Job\Status\Dictionary $status,
        Pipeline\PipelineId $pipelineId,
        \DateTimeImmutable $createdAt,
    ): array {
        $job = file_get_contents(__DIR__ . '/fixture/response/job.200.json');
        $job = json_decode($job, true);

        return [
            'id' => $jobId->value(),
            'name' => (string) $jobName,
            'ref' => (string) $ref,
            'status' => (string) Job\Status\StatusFactory::createStatus($status),
            'created_at' => $createdAt->format(DATE_RFC3339_EXTENDED),
            'pipeline' => [
                'id' => $pipelineId->value(),
            ] + $job['pipeline'],
        ] + $job;
    }
}
