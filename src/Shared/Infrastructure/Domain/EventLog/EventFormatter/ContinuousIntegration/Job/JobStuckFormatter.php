<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\ContinuousIntegration\Job;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job\JobStuck;

/**
 * @extends AbstractJobEventFormatter<JobStuck>
 */
final readonly class JobStuckFormatter extends AbstractJobEventFormatter
{
    public function supports(EventInterface $event): bool
    {
        return $event instanceof JobStuck;
    }

    public function format(EventInterface $event): string
    {
        return "Job `$event->name` {$this->jobKey($event)} stuck in status `$event->status`";
    }
}
