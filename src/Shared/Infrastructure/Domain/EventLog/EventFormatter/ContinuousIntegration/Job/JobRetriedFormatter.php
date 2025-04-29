<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\ContinuousIntegration\Job;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Event\ContinuousIntegration\Job\JobRetried;

/**
 * @extends AbstractJobEventFormatter<JobRetried>
 */
final readonly class JobRetriedFormatter extends AbstractJobEventFormatter
{
    public function supports(EventInterface $event): bool
    {
        return $event instanceof JobRetried;
    }

    public function format(EventInterface $event): string
    {
        return "Job `$event->name` {$this->jobKey($event)} retried";
    }
}
