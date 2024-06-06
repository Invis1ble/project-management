<?php

declare(strict_types=1);

namespace HotfixPublication\Application\UseCase\Command\CreateHotfixPublication;

use Invis1ble\Messenger\Command\TraceableCommandBus;
use Invis1ble\Messenger\Event\TraceableEventBus;
use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Command\CreateHotfixPublication\CreateHotfixPublicationCommand;
use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Command\ProceedToNextStatus\ProceedToNextStatusCommand;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Event\HotfixPublicationCreated;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusCreated;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Repository\HotfixPublicationRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Model\TaskTracker\Issue\CreateIssuesTrait;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CreateHotfixPublicationCommandHandlerTest extends KernelTestCase
{
    use CreateIssuesTrait;

    public function testCreateHotfixPublication(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var HotfixPublicationRepositoryInterface $hotfixPublicationRepository */
        $hotfixPublicationRepository = $container->get(HotfixPublicationRepositoryInterface::class);

        /** @var UriFactoryInterface $uriFactory */
        $uriFactory = $container->get(UriFactoryInterface::class);

        /** @var TraceableCommandBus $commandBus */
        $commandBus = $container->get(TraceableCommandBus::class);

        /** @var TraceableEventBus $eventBus */
        $eventBus = $container->get(TraceableEventBus::class);

        $hotfixes = $this->createIssues(
            uriFactory: $uriFactory,
        );

        $hotfixesArray = $hotfixes->toArray();

        $command = new CreateHotfixPublicationCommand(
            tagName: Tag\VersionName::create(),
            tagMessage: Tag\Message::fromString("{$hotfixesArray[0]->summary} | {$hotfixesArray[0]->key}"),
            hotfixes: $hotfixes,
        );

        $commandBus->dispatch($command);

        $dispatchedEvents = $eventBus->getDispatchedEvents();

        $this->assertCount(1, $dispatchedEvents);

        $dispatchedCommands = $commandBus->getDispatchedCommands();

        $this->assertCount(2, $dispatchedCommands);

        $this->assertArrayHasKey(0, $dispatchedEvents);
        $event0 = $dispatchedEvents[0]->event;
        $this->assertInstanceOf(HotfixPublicationCreated::class, $event0);
        $this->assertObjectEquals(new StatusCreated(), $event0->status);
        $this->assertObjectEquals($command->hotfixes, $event0->hotfixes);

        $publication = $hotfixPublicationRepository->get($event0->id);

        $this->assertObjectEquals($command->tagName, $publication->tagName());
        $this->assertObjectEquals($command->tagMessage, $publication->tagMessage());
        $this->assertObjectEquals($command->hotfixes, $publication->hotfixes());

        $this->assertArrayHasKey(0, $dispatchedCommands);
        $command0 = $dispatchedCommands[0]->command;
        $this->assertInstanceOf(ProceedToNextStatusCommand::class, $command0);
        $this->assertObjectEquals($publication->id(), $command0->id);
    }
}
