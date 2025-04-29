<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Serializer\TaskTracker;

use Invis1ble\ProjectManagement\Shared\Domain\Event\TaskTracker\Issue\IssueTransitioned;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Transition;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Serializer\SerializationTestCase;

/**
 * @extends SerializationTestCase<IssueTransitioned>
 */
class IssueTransitionedSerializationTest extends SerializationTestCase
{
    protected function createObject(): IssueTransitioned
    {
        return new IssueTransitioned(
            projectKey: Project\Key::fromString('TEST'),
            key: Issue\Key::fromString('TEST-1'),
            transitionId: Transition\TransitionId::fromString('123'),
            transitionName: Transition\Name::fromString('Close Issue'),
        );
    }

    protected function objectsEquals(object $object1, object $object2): bool
    {
        return $object1->projectKey->equals($object2->projectKey)
            && $object1->key->equals($object2->key)
            && $object1->transitionId->equals($object2->transitionId)
            && $object1->transitionName->equals($object2->transitionName)
        ;
    }
}
