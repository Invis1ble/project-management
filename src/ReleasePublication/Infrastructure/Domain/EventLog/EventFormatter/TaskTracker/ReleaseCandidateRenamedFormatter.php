<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Infrastructure\Domain\EventLog\EventFormatter\TaskTracker;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\TaskTracker\ReleaseCandidateRenamed;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\AbstractFormatter;

/**
 * @extends AbstractFormatter<ReleaseCandidateRenamed>
 */
final readonly class ReleaseCandidateRenamedFormatter extends AbstractFormatter
{
    public function supports(EventInterface $event): bool
    {
        return $event instanceof ReleaseCandidateRenamed;
    }

    public function format(EventInterface $event): string
    {
        return "Release Candidate renamed to `$event->name`";
    }
}
