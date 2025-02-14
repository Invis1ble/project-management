<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\ContinuousIntegration\Job;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job\AbstractJobEvent;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\AbstractFormatter;

/**
 * @template T of AbstractJobEvent
 *
 * @extends AbstractFormatter<T>
 */
abstract readonly class AbstractJobEventFormatter extends AbstractFormatter
{
    /**
     * @param T $event
     */
    public function jobKey(EventInterface $event): string
    {
        if (null === $event->guiUrl) {
            return "#$event->jobId at $event->projectId";
        }

        return (string) $event->guiUrl;
    }
}
