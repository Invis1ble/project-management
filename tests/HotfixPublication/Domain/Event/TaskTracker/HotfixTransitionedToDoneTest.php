<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\HotfixPublication\Domain\Event\TaskTracker;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Event\TaskTracker\HotfixTransitionedToDone;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Project;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\SerializationTestCase;

/**
 * @extends SerializationTestCase<HotfixTransitionedToDone>
 */
class HotfixTransitionedToDoneTest extends SerializationTestCase
{
    protected function createObject(): HotfixTransitionedToDone
    {
        return new HotfixTransitionedToDone(
            projectKey: Project\Key::fromString('TEST'),
            key: Issue\Key::fromString('TEST-1'),
        );
    }

    protected function objectsEquals(object $object1, object $object2): bool
    {
        return $object1->projectKey->equals($object2->projectKey)
            && $object1->key->equals($object2->key)
        ;
    }
}
