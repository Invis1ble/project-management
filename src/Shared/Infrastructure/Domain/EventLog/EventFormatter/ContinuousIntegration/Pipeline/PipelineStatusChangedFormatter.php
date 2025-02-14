<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\ContinuousIntegration\Pipeline;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline\PipelineStatusChanged;

/**
 * @extends AbstractPipelineEventFormatter<PipelineStatusChanged>
 */
final readonly class PipelineStatusChangedFormatter extends AbstractPipelineEventFormatter
{
    public function supports(EventInterface $event): bool
    {
        return $event instanceof PipelineStatusChanged;
    }

    public function format(EventInterface $event): string
    {
        return "Pipeline {$this->pipelineKey($event)} status changed from `$event->previousStatus` to `$event->status`";
    }
}
