<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\ContinuousIntegration\Job;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;

trait PlayJobResponseFixtureTrait
{
    public function playJobResponseFixture(
        Job\JobId $jobId,
        Job\Name $jobName,
        Ref $ref,
        Job\Status\Dictionary $status,
        \DateTimeImmutable $createdAt,
    ): array {
        $playJob = file_get_contents(__DIR__ . '/fixture/response/play_job.200.json');
        $playJob = json_decode($playJob, true);

        return [
            'id' => $jobId->value(),
            'name' => (string) $jobName,
            'ref' => (string) $ref,
            'status' => (string) Job\Status\StatusFactory::createStatus($status),
            'created_at' => $createdAt->format(DATE_RFC3339_EXTENDED),
        ] + $playJob;
    }
}
