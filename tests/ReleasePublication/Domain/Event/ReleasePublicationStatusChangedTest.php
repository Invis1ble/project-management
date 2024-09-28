<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\ReleasePublication\Domain\Event;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\ReleasePublicationStatusChanged;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusReleaseCandidateCreated;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusReleaseCandidateRenamed;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Issue\CreateIssuesTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\SerializationTestCase;
use Psr\Http\Message\UriFactoryInterface;

/**
 * @extends SerializationTestCase<ReleasePublicationStatusChanged>
 */
class ReleasePublicationStatusChangedTest extends SerializationTestCase
{
    use CreateIssuesTrait;

    protected function createObject(): ReleasePublicationStatusChanged
    {
        $container = $this->getContainer();
        /** @var UriFactoryInterface $uriFactory */
        $uriFactory = $container->get(UriFactoryInterface::class);

        $hotfixes = $this->createIssues(
            uriFactory: $uriFactory,
        );

        $branchName = Branch\Name::fromString('v-1-0-0');

        return new ReleasePublicationStatusChanged(
            id: ReleasePublicationId::fromBranchName($branchName),
            branchName: $branchName,
            tagName: Tag\VersionName::create(),
            tagMessage: Tag\Message::fromString("Release $branchName."),
            previousStatus: new StatusReleaseCandidateRenamed(),
            status: new StatusReleaseCandidateCreated(),
            tasks: $hotfixes,
            createdAt: new \DateTimeImmutable(),
        );
    }

    protected function objectsEquals(object $object1, object $object2): bool
    {
        return $object1->id->equals($object2->id)
            && $object1->branchName->equals($object2->branchName)
            && (null === $object1->tagName ? (null === $object2->tagName) : $object1->tagName->equals($object2->tagName))
            && (null === $object1->tagMessage ? (null === $object2->tagMessage) : $object1->tagMessage->equals($object2->tagMessage))
            && $object1->previousStatus->equals($object2->previousStatus)
            && $object1->status->equals($object2->status)
            && $object1->tasks->equals($object2->tasks)
            // phpcs:disable Symfony.ControlStructure.IdenticalComparison.Warning
            && $object1->createdAt == $object2->createdAt
            // phpcs:enable Symfony.ControlStructure.IdenticalComparison.Warning
        ;
    }
}
