<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\HotfixPublication\Domain\Serializer;

use Invis1ble\ProjectManagement\HotfixPublication\Domain\Event\HotfixPublicationStatusChanged;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationId;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusCreated;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusMergeRequestsMerged;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Issue\CreateIssuesTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Serializer\SerializationTestCase;
use Psr\Http\Message\UriFactoryInterface;

/**
 * @extends SerializationTestCase<HotfixPublicationStatusChanged>
 */
class HotfixPublicationStatusChangedSerializationTest extends SerializationTestCase
{
    use CreateIssuesTrait;

    protected function createObject(): HotfixPublicationStatusChanged
    {
        $container = $this->getContainer();
        /** @var UriFactoryInterface $uriFactory */
        $uriFactory = $container->get(UriFactoryInterface::class);

        $hotfixes = $this->createIssues(
            uriFactory: $uriFactory,
            status: 'Ready for Publish',
        );

        return new HotfixPublicationStatusChanged(
            id: HotfixPublicationId::fromVersionName(Tag\VersionName::create()),
            tagName: Tag\VersionName::fromString('v.25-02-13.0'),
            tagMessage: Tag\Message::fromString('Fix terrible bug'),
            status: new StatusMergeRequestsMerged(),
            previousStatus: new StatusCreated(),
            hotfixes: $hotfixes,
            createdAt: new \DateTimeImmutable(),
        );
    }

    protected function objectsEquals(object $object1, object $object2): bool
    {
        return $object1->id->equals($object2->id)
            && $object1->tagName->equals($object2->tagName)
            && $object1->tagMessage->equals($object2->tagMessage)
            && $object1->status->equals($object2->status)
            && $object1->previousStatus->equals($object2->previousStatus)
            && $object1->hotfixes->equals($object2->hotfixes)
            // phpcs:disable Symfony.ControlStructure.IdenticalComparison.Warning
            && $object1->createdAt == $object2->createdAt
            // phpcs:enable Symfony.ControlStructure.IdenticalComparison.Warning
        ;
    }
}
