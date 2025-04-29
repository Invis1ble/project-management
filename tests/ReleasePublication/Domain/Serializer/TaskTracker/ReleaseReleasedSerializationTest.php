<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\ReleasePublication\Domain\Serializer\TaskTracker;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\TaskTracker\VersionReleased;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Serializer\SerializationTestCase;

/**
 * @extends SerializationTestCase<VersionReleased>
 */
class ReleaseReleasedSerializationTest extends SerializationTestCase
{
    protected function createObject(): VersionReleased
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

    protected function objectsEquals(object $object1, object $object2): bool
    {
        return $object1->id->equals($object2->id)
            && $object1->name->equals($object2->name)
            && $object1->description->equals($object2->description)
            && $object1->archived === $object2->archived
            && $object1->released === $object2->released
            // phpcs:disable Symfony.ControlStructure.IdenticalComparison.Warning
            && $object1->releaseDate == $object2->releaseDate
            // phpcs:enable Symfony.ControlStructure.IdenticalComparison.Warning
        ;
    }
}
