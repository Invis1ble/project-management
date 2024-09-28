<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\HotfixPublication\Application\UseCase\Command\CreateHotfixPublication;

use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Command\CreateHotfixPublication\CreateHotfixPublicationCommand;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\Model\TaskTracker\Issue\CreateIssuesTrait;
use Invis1ble\ProjectManagement\Tests\Shared\Domain\SerializationTestCase;
use Psr\Http\Message\UriFactoryInterface;

/**
 * @extends SerializationTestCase<CreateHotfixPublicationCommand>
 */
class CreateHotfixPublicationCommandTest extends SerializationTestCase
{
    use CreateIssuesTrait;

    protected function createObject(): CreateHotfixPublicationCommand
    {
        $container = $this->getContainer();
        /** @var UriFactoryInterface $uriFactory */
        $uriFactory = $container->get(UriFactoryInterface::class);

        $hotfixes = $this->createIssues(
            uriFactory: $uriFactory,
            status: 'Ready for Publish',
        );

        $hotfixesArray = $hotfixes->toArray();

        return new CreateHotfixPublicationCommand(
            tagName: Tag\VersionName::create(),
            tagMessage: Tag\Message::fromString("{$hotfixesArray[0]->summary} | {$hotfixesArray[0]->key}"),
            hotfixes: $hotfixes,
        );
    }

    protected function objectsEquals(object $object1, object $object2): bool
    {
        return $object1->tagName->equals($object2->tagName)
            && $object1->tagMessage->equals($object2->tagMessage)
            && $object1->hotfixes->equals($object2->hotfixes)
        ;
    }
}
