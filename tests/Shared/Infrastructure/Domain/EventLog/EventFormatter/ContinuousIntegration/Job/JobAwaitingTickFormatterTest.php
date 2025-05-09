<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\ContinuousIntegration\Job;

use GuzzleHttp\Psr7\Uri;
use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job\JobAwaitingTick;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Job;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\ContinuousIntegration\Job\JobAwaitingTickFormatter;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\EventFormatterTestCase;

/**
 * @extends EventFormatterTestCase<JobAwaitingTick>
 */
class JobAwaitingTickFormatterTest extends EventFormatterTestCase
{
    protected function createEventFormatter(): JobAwaitingTickFormatter
    {
        return new JobAwaitingTickFormatter();
    }

    protected function createEvent(): JobAwaitingTick
    {
        return new JobAwaitingTick(
            projectId: Project\ProjectId::from(1),
            ref: Ref::fromString('1234567890abcdef1234567890abcdef12345678'),
            pipelineId: Pipeline\PipelineId::from(3),
            jobId: Job\JobId::from(4),
            name: Job\Name::fromString('deploy'),
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
        return "Job `$event->name` $event->guiUrl awaiting tick";
    }
}
