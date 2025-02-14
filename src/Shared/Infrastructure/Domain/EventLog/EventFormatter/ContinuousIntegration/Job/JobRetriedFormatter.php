<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\ContinuousIntegration\Job;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job\JobRetried;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\AbstractFormatter;

/**
 * @extends AbstractFormatter<JobRetried>
 */
final readonly class JobRetriedFormatter extends AbstractFormatter
{
    public function supports(EventInterface $event): bool
    {
        return $event instanceof JobRetried;
    }

    public function format(EventInterface $event): string
    {
        if (null === $event->guiUrl) {
            $key = "#$event->jobId at $event->projectId";
        } else {
            $key = $event->guiUrl;
        }

        return "Job `$event->name` $key retried";
    }
}
