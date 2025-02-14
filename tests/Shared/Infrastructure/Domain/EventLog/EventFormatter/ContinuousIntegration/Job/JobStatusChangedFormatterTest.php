<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\ContinuousIntegration\Job;

use GuzzleHttp\Psr7\Uri;
use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job\JobStatusChanged;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\ContinuousIntegration\Job\JobStatusChangedFormatter;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\EventFormatterTestCase;

/**
 * @extends EventFormatterTestCase<JobStatusChanged>
 */
class JobStatusChangedFormatterTest extends EventFormatterTestCase
{
    protected function createEventFormatter(): JobStatusChangedFormatter
    {
        return new JobStatusChangedFormatter();
    }

    protected function createEvent(): JobStatusChanged
    {
        return new JobStatusChanged(
            projectId: Project\ProjectId::from(1),
            ref: Ref::fromString('1234567890abcdef1234567890abcdef12345678'),
            pipelineId: Pipeline\PipelineId::from(3),
            jobId: Job\JobId::from(4),
            name: Job\Name::fromString('deploy'),
            previousStatus: new Job\Status\StatusPreparing(),
            status: new Job\Status\StatusPending(),
            guiUrl: new Uri('https://example.com/foo/bar/-/jobs/4'),
            createdAt: new \DateTimeImmutable('-1 hour'),
            startedAt: new \DateTimeImmutable('-30 minutes'),
            finishedAt: new \DateTimeImmutable(),
            maxAwaitingTime: new \DateInterval('PT30M'),
        );
    }

    protected function createExpectedMessage(EventInterface $event): string
    {
        return "Job `$event->name` $event->guiUrl status changed from `$event->previousStatus` to `$event->status`";
    }
}
