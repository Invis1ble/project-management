<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\ReleasePublication\Infrastructure\Domain\EventLog\EventFormatter\TaskTracker;

use Invis1ble\Messenger\Event\EventInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\TaskTracker\ReleaseCandidateCreated;
use Invis1ble\ProjectManagement\ReleasePublication\Infrastructure\Domain\EventLog\EventFormatter\TaskTracker\ReleaseCandidateCreatedFormatter;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\EventLog\EventFormatter\EventFormatterTestCase;

/**
 * @extends EventFormatterTestCase<ReleaseCandidateCreated>
 */
class ReleaseCandidateCreatedFormatterTest extends EventFormatterTestCase
{
    protected function createEventFormatter(): ReleaseCandidateCreatedFormatter
    {
        return new ReleaseCandidateCreatedFormatter();
    }

    protected function createEvent(): ReleaseCandidateCreated
    {
        return new ReleaseCandidateCreated(
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
        return 'Release Candidate created';
    }
}
