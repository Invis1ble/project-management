<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\ReleasePublication\Infrastructure\Domain\EventLog\EventFormatter;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\TaskTracker\VersionReleased;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version;
use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\EventLog\EventFormatter\BranchCreatedFormatter;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\EventFormatterTestCase;

/**
 * @extends EventFormatterTestCase<VersionReleased>
 */
class VersionReleasedFormatterTest extends EventFormatterTestCase
{
    protected function createEventFormatter(): BranchCreatedFormatter
    {
        return new BranchCreatedFormatter();
    }

    protected function createEvent(): VersionReleased
    {
        return new VersionReleased(
            id: Version\VersionId::fromString('1000'),
            name: Version\Name::fromString('v-1-0-0'),
            description: Version\Description::fromString('Version v-1-0-0'),
            archived: false,
            released: true,
            releaseDate: new \DateTimeImmutable(),
        );
    }

    protected function createExpectedMessage(EventInterface $event): string
    {
        return "Version `$event->name` released";
    }
}
