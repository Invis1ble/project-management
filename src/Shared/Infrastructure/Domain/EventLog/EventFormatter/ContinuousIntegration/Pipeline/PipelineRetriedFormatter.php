<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\ContinuousIntegration\Pipeline;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline\PipelineRetried;

/**
 * @extends AbstractPipelineEventFormatter<PipelineRetried>
 */
final readonly class PipelineRetriedFormatter extends AbstractPipelineEventFormatter
{
    public function supports(EventInterface $event): bool
    {
        return $event instanceof PipelineRetried;
    }

    public function format(EventInterface $event): string
    {
        return "Pipeline {$this->pipelineKey($event)} retried";
    }
}
