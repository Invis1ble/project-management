<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\ReleasePublication\Domain\Event;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Event\ReleasePublicationCreated;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status\StatusCreated;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Issue\CreateIssuesTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\SerializationTestCase;
use Psr\Http\Message\UriFactoryInterface;

/**
 * @extends SerializationTestCase<ReleasePublicationCreated>
 */
class ReleasePublicationCreatedTest extends SerializationTestCase
{
    use CreateIssuesTrait;

    protected function createObject(): ReleasePublicationCreated
    {
        $container = $this->getContainer();
        /** @var UriFactoryInterface $uriFactory */
        $uriFactory = $container->get(UriFactoryInterface::class);

        $hotfixes = $this->createIssues(
            uriFactory: $uriFactory,
        );

        $branchName = Name::fromString('v-1-0-0');

        return new ReleasePublicationCreated(
            id: ReleasePublicationId::fromBranchName($branchName),
            branchName: $branchName,
            status: new StatusCreated(),
            readyToMergeTasks: $hotfixes,
            createdAt: new \DateTimeImmutable(),
        );
    }

    protected function objectsEquals(object $object1, object $object2): bool
    {
        return $object1->id->equals($object2->id)
            && $object1->branchName->equals($object2->branchName)
            && $object1->status->equals($object2->status)
            && $object1->readyToMergeTasks->equals($object2->readyToMergeTasks)
            // phpcs:disable Symfony.ControlStructure.IdenticalComparison.Warning
            && $object1->createdAt == $object2->createdAt
            // phpcs:enable Symfony.ControlStructure.IdenticalComparison.Warning
        ;
    }
}
