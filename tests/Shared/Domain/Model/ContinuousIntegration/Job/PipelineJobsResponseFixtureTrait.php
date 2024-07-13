<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\ContinuousIntegration\Job;

use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;

trait PipelineJobsResponseFixtureTrait
{
    public function pipelineJobsResponseFixture(Job\Name $jobName): array
    {
        $pipelineJobs = file_get_contents(__DIR__ . '/fixture/response/pipeline_jobs.200.json');
        $pipelineJobs = json_decode($pipelineJobs, true);

        $pipelineJobs[0]['name'] = $jobName;

        return $pipelineJobs;
    }
}
