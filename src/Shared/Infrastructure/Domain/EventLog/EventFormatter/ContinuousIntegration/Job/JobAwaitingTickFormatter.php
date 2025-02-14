<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\ContinuousIntegration\Job;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job\JobAwaitingTick;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\AbstractFormatter;

/**
 * @extends AbstractFormatter<JobAwaitingTick>
 */
final readonly class JobAwaitingTickFormatter extends AbstractFormatter
{
    public function supports(EventInterface $event): bool
    {
        return $event instanceof JobAwaitingTick;
    }

    public function format(EventInterface $event): string
    {
        if (null === $event->guiUrl) {
            $key = "#$event->jobId at $event->projectId";
        } else {
            $key = $event->guiUrl;
        }

        return "Job `$event->name` $key awaiting tick";
    }
}
