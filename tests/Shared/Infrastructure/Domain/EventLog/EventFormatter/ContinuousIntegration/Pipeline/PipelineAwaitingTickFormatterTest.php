<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\ContinuousIntegration\Pipeline;

use GuzzleHttp\Psr7\Uri;
use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline\PipelineAwaitingTick;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\ContinuousIntegration\Pipeline\PipelineAwaitingTickFormatter;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\EventFormatterTestCase;

/**
 * @extends EventFormatterTestCase<PipelineAwaitingTick>
 */
class PipelineAwaitingTickFormatterTest extends EventFormatterTestCase
{
    protected function createEventFormatter(): PipelineAwaitingTickFormatter
    {
        return new PipelineAwaitingTickFormatter();
    }

    protected function createEvent(): PipelineAwaitingTick
    {
        return new PipelineAwaitingTick(
            projectId: Project\ProjectId::from(1),
            ref: Ref::fromString('1234567890abcdef1234567890abcdef12345678'),
            pipelineId: Pipeline\PipelineId::from(2),
            status: Pipeline\Status::Created,
            guiUrl: new Uri('http://127.0.0.1:3000/test-group/test-project/-/pipelines/2'),
            maxAwaitingTime: new \DateInterval('PT30M'),
        );
    }

    protected function createExpectedMessage(EventInterface $event): string
    {
        return "Pipeline $event->guiUrl awaiting tick";
    }
}
