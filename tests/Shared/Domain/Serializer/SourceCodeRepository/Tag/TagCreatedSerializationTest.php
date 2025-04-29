<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Serializer\SourceCodeRepository\Tag;

use Invis1ble\ProjectManagement\Shared\Domain\Event\SourceCodeRepository\Tag\TagCreated;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Ref;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Serializer\SerializationTestCase;

/**
 * @extends SerializationTestCase<TagCreated>
 */
class TagCreatedSerializationTest extends SerializationTestCase
{
    protected function createObject(): TagCreated
    {
        return new TagCreated(
            projectId: Project\ProjectId::from(1),
            name: Tag\Name::fromString('v1.0.0'),
            ref: Ref::fromString('1234567890abcdef1234567890abcdef12345678'),
            message: Tag\Message::fromString('Release v1.0.0'),
            createdAt: new \DateTimeImmutable(),
        );
    }

    protected function objectsEquals(object $object1, object $object2): bool
    {
        return $object1->projectId->equals($object2->projectId)
            && $object1->name->equals($object2->name)
            && $object1->ref->equals($object2->ref)
            && $object1->message->equals($object2->message)
            // phpcs:disable Symfony.ControlStructure.IdenticalComparison.Warning
            && $object1->createdAt == $object2->createdAt
            // phpcs:enable Symfony.ControlStructure.IdenticalComparison.Warning
        ;
    }
}
