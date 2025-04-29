<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Infrastructure\Domain\EventLog\EventFormatter\TaskTracker;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\TaskTracker\VersionReleased;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\AbstractFormatter;

/**
 * @extends AbstractFormatter<VersionReleased>
 */
final readonly class VersionReleasedFormatter extends AbstractFormatter
{
    public function supports(EventInterface $event): bool
    {
        return $event instanceof VersionReleased;
    }

    public function format(EventInterface $event): string
    {
        return "Version `$event->name` released";
    }
}
