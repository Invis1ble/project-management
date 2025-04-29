<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\ReleasePublication\Infrastructure\Domain\EventLog\EventFormatter\TaskTracker;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\TaskTracker\ReleaseCandidateRenamed;
use Invis1ble\ProjectManagement\ReleasePublication\Infrastructure\Domain\EventLog\EventFormatter\TaskTracker\ReleaseCandidateRenamedFormatter;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\EventFormatterTestCase;

/**
 * @extends EventFormatterTestCase<ReleaseCandidateRenamed>
 */
class ReleaseCandidateRenamedFormatterTest extends EventFormatterTestCase
{
    protected function createEventFormatter(): ReleaseCandidateRenamedFormatter
    {
        return new ReleaseCandidateRenamedFormatter();
    }

    protected function createEvent(): ReleaseCandidateRenamed
    {
        return new ReleaseCandidateRenamed(
            id: Version\VersionId::fromString('1000'),
            previousName: Version\Name::fromString('Release Candidate'),
            name: Version\Name::fromString('v-1-0-0'),
            description: Version\Description::fromString('Version v-1-0-0'),
            archived: false,
            released: true,
            releaseDate: new \DateTimeImmutable(),
        );
    }

    protected function createExpectedMessage(EventInterface $event): string
    {
        return "Release Candidate renamed to `$event->name`";
    }
}
