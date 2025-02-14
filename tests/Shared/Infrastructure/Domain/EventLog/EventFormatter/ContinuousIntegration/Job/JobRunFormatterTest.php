<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\ContinuousIntegration\Job;

use GuzzleHttp\Psr7\Uri;
use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job\JobRun;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\ContinuousIntegration\Job\JobRunFormatter;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\EventFormatterTestCase;

/**
 * @extends EventFormatterTestCase<JobRun>
 */
class JobRunFormatterTest extends EventFormatterTestCase
{
    protected function createEventFormatter(): JobRunFormatter
    {
        return new JobRunFormatter();
    }

    protected function createEvent(): JobRun
    {
        return new JobRun(
            projectId: Project\ProjectId::from(1),
            ref: Ref::fromString('1234567890abcdef1234567890abcdef12345678'),
            jobId: Job\JobId::from(3),
            name: Job\Name::fromString('deploy'),
            status: new Job\Status\StatusPending(),
            guiUrl: new Uri('https://example.com/foo/bar/-/jobs/3'),
            createdAt: new \DateTimeImmutable('-1 hour'),
            startedAt: new \DateTimeImmutable('-30 minutes'),
            finishedAt: new \DateTimeImmutable(),
        );
    }

    protected function createExpectedMessage(EventInterface $event): string
    {
        return "Job `$event->name` $event->guiUrl run";
    }
}
