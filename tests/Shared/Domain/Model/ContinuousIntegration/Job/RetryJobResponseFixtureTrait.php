<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\ContinuousIntegration\Job;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;

trait RetryJobResponseFixtureTrait
{
    public function retryJobResponseFixture(
        Job\JobId $jobId,
        Job\Name $jobName,
        Ref $ref,
        Job\Status\Dictionary $status,
        \DateTimeImmutable $createdAt,
    ): array {
        $retryJob = file_get_contents(__DIR__ . '/fixture/response/retry_job.200.json');
        $retryJob = json_decode($retryJob, true);

        return [
            'id' => $jobId->value(),
            'name' => (string) $jobName,
            'ref' => (string) $ref,
            'status' => (string) Job\Status\StatusFactory::createStatus($status),
            'created_at' => $createdAt->format(DATE_RFC3339_EXTENDED),
        ] + $retryJob;
    }
}
