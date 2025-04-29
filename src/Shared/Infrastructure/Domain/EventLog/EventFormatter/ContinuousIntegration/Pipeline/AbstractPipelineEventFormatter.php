<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\ContinuousIntegration\Pipeline;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline\AbstractPipelineEvent;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Pipeline\PipelineStuck;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\AbstractFormatter;

/**
 * @template T of AbstractPipelineEvent|PipelineStuck
 *
 * @extends AbstractFormatter<T>
 */
abstract readonly class AbstractPipelineEventFormatter extends AbstractFormatter
{
    /**
     * @param T $event
     */
    public function pipelineKey(EventInterface $event): string
    {
        if (null === $event->guiUrl) {
            return "#$event->pipelineId at $event->projectId";
        }

        return (string) $event->guiUrl;
    }
}
